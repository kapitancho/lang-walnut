module db-sql-query-builder %% db:

SqlString = String;

SqlQuoter = [quoteIdentifier: ^String => String, quoteValue: ^String|Integer|Real|Boolean|Null => String];

SqlValue = $[value: String|Integer|Real|Boolean|Null];
SqlValue ==> SqlString %% [~SqlQuoter] :: %sqlQuoter.quoteValue($.value);
PreparedValue = $[parameterName: String];
PreparedValue ==> SqlString :: ':'->concat($.parameterName);
QueryValue = SqlValue|PreparedValue;

DatabaseTableName = String<1..>;
DatabaseFieldName = String<1..>;

InsertQuery = $[tableName: DatabaseTableName, values: Map<QueryValue>];
InsertQuery ==> DatabaseSqlQuery %% [~SqlQuoter] :: 'INSERT INTO '
    ->concatList[
        $tableName,
        ' (',
        $values->keys->map(^String => String :: %sqlQuoter.quoteIdentifier(#))->combineAsString(', '),
        ') VALUES (',
        $values->values->map(^QueryValue => String :: #->asSqlString)->combineAsString(', '),
        ')'
    ];

TableField = $[tableAlias: DatabaseTableName, fieldName: DatabaseFieldName];
TableField ==> SqlString %% [~SqlQuoter] :: [
    %sqlQuoter.quoteIdentifier($.tableAlias), %sqlQuoter.quoteIdentifier($.fieldName)
]->combineAsString('.');

SqlFieldExpressionOperation = :[Equals, NullSafeEquals, NotEquals, LessThan, LessOrEquals, GreaterThan, GreaterOrEquals, Like, NotLike, Regexp];
SqlFieldExpressionOperation ==> SqlString :: ?whenValueOf($) is {
	SqlFieldExpressionOperation.Equals: '=',
	SqlFieldExpressionOperation.NullSafeEquals: '<=>',
	SqlFieldExpressionOperation.NotEquals: '!=',
	SqlFieldExpressionOperation.LessThan: '<',
	SqlFieldExpressionOperation.LessOrEquals: '<=',
	SqlFieldExpressionOperation.GreaterThan: '>',
	SqlFieldExpressionOperation.GreaterOrEquals: '>=',
	SqlFieldExpressionOperation.Like: 'LIKE',
	SqlFieldExpressionOperation.NotLike: 'NOT LIKE',
	SqlFieldExpressionOperation.Regexp: 'REGEXP'
};

SqlFieldExpression = $[
    fieldName: String|TableField,
    operation: SqlFieldExpressionOperation,
    value: String|TableField|QueryValue
];
SqlRawExpression = $[expression: String];
SqlAndExpression = $[expressions: Array<`SqlExpression>];
SqlOrExpression = $[expressions: Array<`SqlExpression>];
SqlNotExpression = $[expression: `SqlExpression];
SqlExpression = SqlRawExpression|SqlAndExpression|SqlOrExpression|SqlNotExpression|SqlFieldExpression;

SqlFieldExpression ==> SqlString :: [
    $fieldName->as(type{SqlString}),
    $operation->as(type{SqlString}),
    $value->as(type{SqlString})
]->combineAsString(' ');
SqlRawExpression ==> SqlString :: $.expression;
SqlAndExpression ==> SqlString :: ?whenTypeOf($expressions) is {
    type{Array<SqlExpression, 1..>}: [
        '(',
        $expressions->map(^SqlExpression => String :: #->asSqlString)->combineAsString(' AND '),
        ')'
    ]->combineAsString(''),
    ~: '1'
};
SqlOrExpression ==> SqlString :: ?whenTypeOf($expressions) is {
    type{Array<SqlExpression, 1..>}: [
        '(',
        $expressions->map(^SqlExpression => String :: #->asSqlString)->combineAsString(' OR '),
        ')'
    ]->combineAsString(''),
    ~: '0'
};
SqlNotExpression ==> SqlString :: ['NOT (', $expression->asSqlString, ')']->combineAsString('');

SqlQueryFilter = $[expression: SqlExpression];
SqlQueryFilter ==> SqlString :: $expression->asSqlString;

UpdateQuery = $[tableName: DatabaseTableName, values: Map<QueryValue>, queryFilter: SqlQueryFilter];
UpdateQuery ==> DatabaseSqlQuery %% [~SqlQuoter] :: 'UPDATE '
    ->concatList[
        $tableName,
        ' SET ',
        $values->mapKeyValue(^[key: String, value: QueryValue] => String :: ''->concatList[
            %sqlQuoter.quoteIdentifier(#key), ' = ', #value->asSqlString
        ])->values->combineAsString(', '),
        ' WHERE ',
        $queryFilter->asSqlString
    ];

DeleteQuery = $[tableName: DatabaseTableName, queryFilter: SqlQueryFilter];
DeleteQuery ==> DatabaseSqlQuery %% [~SqlQuoter] :: 'DELETE FROM '
    ->concatList[
        $tableName,
        ' WHERE ',
        $queryFilter->asSqlString
    ];

SqlSelectLimit = $[limit: Integer<1..>, offset: Integer<0..>];
SqlSelectLimit ==> SqlString :: ['LIMIT', $.limit->asString, 'OFFSET', $.offset->asString]->combineAsString(' ');

SqlOrderByDirection = :[Asc, Desc];
SqlOrderByDirection ==> SqlString :: ?whenValueOf($) is {
    SqlOrderByDirection.Asc: 'ASC',
    SqlOrderByDirection.Desc: 'DESC'
};
SqlOrderByField = $[field: DatabaseFieldName, direction: SqlOrderByDirection];
SqlOrderByField ==> SqlString %% [~SqlQuoter] :: [
    %sqlQuoter.quoteIdentifier($.field),
    $.direction->asSqlString
]->combineAsString(' ');
SqlOrderByFields = $[fields: Array<SqlOrderByField>];
SqlOrderByFields ==> SqlString :: 'ORDER BY '->concat(
    $fields->map(^SqlOrderByField => String :: #->asSqlString)->combineAsString(', ')
);

SqlTableJoinType = :[Inner, Left, Right, Full];
SqlTableJoinType ==> SqlString :: ?whenValueOf($) is {
    SqlTableJoinType.Inner: 'JOIN',
    SqlTableJoinType.Left: 'LEFT JOIN',
    SqlTableJoinType.Right: 'RIGHT JOIN',
    SqlTableJoinType.Full: 'FULL JOIN'
};
SqlTableJoin = $[
    tableAlias: DatabaseTableName,
    tableName: DatabaseTableName,
    joinType: SqlTableJoinType,
    queryFilter: SqlQueryFilter
];
SqlTableJoin ==> SqlString %% [~SqlQuoter] :: [
    $joinType->asSqlString, ' ',
    %sqlQuoter.quoteIdentifier($.tableName), ' AS ', %sqlQuoter.quoteIdentifier($.tableAlias),
    ' ON ', $.queryFilter->asSqlString
]->combineAsString(' ');

SqlSelectFieldList = $[fields: Map<DatabaseFieldName|TableField|QueryValue>];
SqlSelectFieldList ==> SqlString %% [~SqlQuoter] :: $fields->mapKeyValue(^[key: String, value: DatabaseFieldName|TableField|QueryValue] => String :: ''->concatList[
    ?whenTypeOf(#value) is {
        type{DatabaseFieldName}: %sqlQuoter.quoteIdentifier(#value),
        type{TableField|QueryValue}: #value->asSqlString
    },
    ' AS ', %sqlQuoter.quoteIdentifier(#key)
])->values->combineAsString(', ');
SelectQuery = $[
    tableName: DatabaseTableName,
    fields: SqlSelectFieldList,
    joins: Array<SqlTableJoin>,
    queryFilter: SqlQueryFilter,
    orderBy: SqlOrderByFields|Null,
    limit: SqlSelectLimit|Null
];
SelectQuery ==> DatabaseSqlQuery %% [~SqlQuoter] :: [
    'SELECT',
    $fields->asSqlString,
    'FROM', %sqlQuoter.quoteIdentifier($tableName),
    $joins->map(^SqlTableJoin => String :: #->asSqlString)->combineAsString(' '),
    'WHERE', $queryFilter->asSqlString,
    ?whenTypeOf($orderBy) is {
        type{SqlOrderByFields}: $orderBy->asSqlString,
        ~: ''
    },
    ?whenTypeOf($limit) is {
        type{SqlSelectLimit}: $limit->asSqlString,
        ~: ''
    }
]->combineAsString(' ');