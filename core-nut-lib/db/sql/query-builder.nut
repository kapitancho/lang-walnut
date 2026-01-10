module $db/sql/query-builder %% $db/core:

SqlString = String;

SqlQuoter = [quoteIdentifier: ^String => String, quoteValue: ^String|Integer|Real|Boolean|Null => String];

SqlValue := $[value: String|Integer|Real|Boolean|Null];
SqlValue ==> SqlString %% ~SqlQuoter :: sqlQuoter.quoteValue($value);
PreparedValue := $[parameterName: String];
PreparedValue ==> SqlString :: ':'->concat($parameterName);
QueryValue = SqlValue|PreparedValue;

DatabaseTableName = String<1..>;
DatabaseFieldName = String<1..>;

InsertQuery := $[tableName: DatabaseTableName, values: Map<QueryValue>];
InsertQuery ==> DatabaseSqlQuery %% ~SqlQuoter ::
    [
        tableName: sqlQuoter.quoteIdentifier($tableName),
        columns: $values->keys->map(^k: String => String :: sqlQuoter.quoteIdentifier(k))->combineAsString(', '),
        valueStrings: $values->values->map(^qv: QueryValue => String :: qv->asSqlString)->combineAsString(', ')
    ]->format('INSERT INTO {tableName} ({columns}) VALUES ({valueStrings})');

TableField := $[tableAlias: DatabaseTableName, fieldName: DatabaseFieldName];
TableField ==> SqlString %% ~SqlQuoter ::
    sqlQuoter.quoteIdentifier($tableAlias) + '.' + sqlQuoter.quoteIdentifier($fieldName);

SqlFieldExpressionOperation := (Equals, NullSafeEquals, NotEquals, LessThan, LessOrEquals, GreaterThan, GreaterOrEquals, Like, NotLike, Regexp);
SqlFieldExpressionOperation ==> SqlString :: ?whenValueOf($) {
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

SqlFieldExpression := $[
    fieldName: String|TableField,
    operation: SqlFieldExpressionOperation,
    value: String|TableField|QueryValue
];
SqlRawExpression := $[expression: String];
SqlAndExpression := $[expressions: Array<\SqlExpression>];
SqlOrExpression := $[expressions: Array<\SqlExpression>];
SqlNotExpression := $[expression: \SqlExpression];
SqlExpression = SqlRawExpression|SqlAndExpression|SqlOrExpression|SqlNotExpression|SqlFieldExpression;

SqlFieldExpression ==> SqlString :: [
    $fieldName->as(`SqlString),
    $operation->as(`SqlString),
    $value->as(`SqlString)
]->combineAsString(' ');
SqlRawExpression ==> SqlString :: $expression;
SqlAndExpression ==> SqlString :: ?whenTypeOf($expressions) {
    `Array<SqlExpression, 1..>: '(' + $expressions->map(^SqlExpression => String :: #->asSqlString)->combineAsString(' AND ') + ')',
    ~: '1'
};
SqlOrExpression ==> SqlString :: ?whenTypeOf($expressions) {
    `Array<SqlExpression, 1..>: '(' + $expressions->map(^SqlExpression => String :: #->asSqlString)->combineAsString(' OR ') + ')',
    ~: '0'
};
SqlNotExpression ==> SqlString :: 'NOT (' + $expression->asSqlString + ')';

SqlQueryFilter := $[expression: SqlExpression];
SqlQueryFilter ==> SqlString :: $expression->asSqlString;

UpdateQuery := $[tableName: DatabaseTableName, values: Map<QueryValue>, queryFilter: SqlQueryFilter];
UpdateQuery ==> DatabaseSqlQuery %% ~SqlQuoter :: [
    tableName: sqlQuoter.quoteIdentifier($tableName),
    setClauses: $values->mapKeyValue(^[key: String, value: QueryValue] => String :: ''->concatList[
        sqlQuoter.quoteIdentifier(#key), ' = ', #value->asSqlString
    ])->values->combineAsString(', '),
    whereClause: $queryFilter->asSqlString
]->format('UPDATE {tableName} SET {setClauses} WHERE {whereClause}');

DeleteQuery := $[tableName: DatabaseTableName, queryFilter: SqlQueryFilter];
DeleteQuery ==> DatabaseSqlQuery %% ~SqlQuoter :: [
    tableName: sqlQuoter.quoteIdentifier($tableName),
    whereClause: $queryFilter->asSqlString
]->format('DELETE FROM {tableName} WHERE {whereClause}');

SqlSelectLimit := $[limit: Integer<1..>, offset: Integer<0..>];
SqlSelectLimit ==> SqlString :: [limit: $limit, offset: $offset]->format('LIMIT {limit} OFFSET {offset}');

SqlOrderByDirection := (Asc, Desc);
SqlOrderByDirection ==> SqlString :: ?whenValueOf($) {
    SqlOrderByDirection.Asc: 'ASC',
    SqlOrderByDirection.Desc: 'DESC'
};
SqlOrderByField := $[field: DatabaseFieldName, direction: SqlOrderByDirection];
SqlOrderByField ==> SqlString %% ~SqlQuoter :: [
    sqlQuoter.quoteIdentifier($field),
    $direction->asSqlString
]->combineAsString(' ');
SqlOrderByFields := $[fields: Array<SqlOrderByField>];
SqlOrderByFields ==> SqlString :: 'ORDER BY '->concat(
    $fields->map(^SqlOrderByField => String :: #->asSqlString)->combineAsString(', ')
);

SqlTableJoinType := (Inner, Left, Right, Full);
SqlTableJoinType ==> SqlString :: ?whenValueOf($) {
    SqlTableJoinType.Inner: 'JOIN',
    SqlTableJoinType.Left: 'LEFT JOIN',
    SqlTableJoinType.Right: 'RIGHT JOIN',
    SqlTableJoinType.Full: 'FULL JOIN'
};
SqlTableJoin := $[
    tableAlias: DatabaseTableName,
    tableName: DatabaseTableName,
    joinType: SqlTableJoinType,
    queryFilter: SqlQueryFilter
];
SqlTableJoin ==> SqlString %% ~SqlQuoter :: [
    joinType: $joinType->asSqlString,
    tableName: sqlQuoter.quoteIdentifier($tableName),
    tableAlias: sqlQuoter.quoteIdentifier($tableAlias),
    queryFilter: $queryFilter->asSqlString
]->format('{joinType} {tableName} AS {tableAlias} ON {queryFilter}');

SqlSelectFieldList := $[fields: Map<DatabaseFieldName|TableField|QueryValue>];
SqlSelectFieldList ==> SqlString %% ~SqlQuoter :: $fields->mapKeyValue(^[key: String, value: DatabaseFieldName|TableField|QueryValue] => String :: ''->concatList[
    ?whenTypeOf(#value) {
        `DatabaseFieldName: sqlQuoter.quoteIdentifier(#value),
        `TableField|QueryValue: #value->asSqlString
    },
    ' AS ', sqlQuoter.quoteIdentifier(#key)
])->values->combineAsString(', ');
SelectQuery := $[
    tableName: DatabaseTableName,
    fields: SqlSelectFieldList,
    joins: Array<SqlTableJoin>,
    queryFilter: SqlQueryFilter,
    orderBy: SqlOrderByFields|Null,
    limit: SqlSelectLimit|Null
];
SelectQuery ==> DatabaseSqlQuery %% ~SqlQuoter :: [
    fields: $fields->asSqlString,
    tableName: sqlQuoter.quoteIdentifier($tableName),
    joins: ?whenTypeOf($joins) { `Array<1..> : ' ', ~: '' } +
        $joins->map(^SqlTableJoin => SqlString :: #->asSqlString)->combineAsString(' '),
    queryFilter: $queryFilter->asSqlString,
    orderBy: ?whenTypeOf($orderBy) {
        `SqlOrderByFields: ' ' + $orderBy->asSqlString,
        ~: ''
    },
    limit: ?whenTypeOf($limit) {
        `SqlSelectLimit: ' ' + $limit->asSqlString,
        ~: ''
    }
]->format('SELECT {fields} FROM {tableName}{joins} WHERE {queryFilter}{orderBy}{limit}');
