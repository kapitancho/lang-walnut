module $db/orm/xorm %% $db/core, $db/sql/query-builder, $db/sql/quoter-mysql:

FieldTypes = Map<Type>;
UnknownFieldTypes := ();

UnknownOrmModel := Type;
OrmModel := #[table: DatabaseTableName, keyField: DatabaseFieldName, sequenceField: ?DatabaseFieldName];
OrmModel->orderBy(=> SqlOrderByFields|Null) :: ?whenTypeOf($sequenceField) {
    `String<1..>: SqlOrderByFields[[SqlOrderByField[$sequenceField, SqlOrderByDirection.Asc]]]
};
OrmModel->filterByKeyField(=> SqlQueryFilter) :: SqlQueryFilter[SqlFieldExpression[
    TableField[$table, $keyField], SqlFieldExpressionOperation.Equals, PreparedValue[$keyField]
]];


Ox := $[~OrmModel, ~FieldTypes];
Ox[~Type] @ UnknownOrmModel|UnknownFieldTypes :: {
    ormModel = #type->as(`OrmModel);
    ormModel = ?whenTypeOf(ormModel) {
        `OrmModel: ormModel,
        ~: => @UnknownOrmModel!#type
    };

    fieldTypesHelper = ^ t: Type => Result<Map<Type>, UnknownFieldTypes> :: {
        ?whenTypeOf(t) {
            `Type<Data>: fieldTypesHelper(t->valueType)?,
            `Type<Open>: fieldTypesHelper(t->valueType)?,
            `Type<Record>: t->itemTypes,
            `Type<Alias>: fieldTypesHelper(t->aliasedType)?,
            `Type<Type>: fieldTypesHelper(t->refType)?,
            ~: @UnknownFieldTypes
        }
    };
    fieldTypes = fieldTypesHelper(#type)?;
    [ormModel: ormModel, fieldTypes: fieldTypes]
};
Ox->keyField(=> DatabaseFieldName) :: $ormModel.keyField;

FieldTypes->forSelect(^[table: DatabaseTableName] => SqlSelectFieldList) :: {
    fields = $->mapKeyValue(^[key: String, value: Any] => TableField|String<1..> :: ?whenTypeOf(#key) {
        `DatabaseFieldName: TableField[#table, #key],
        ~: '1'
    });
    SqlSelectFieldList[fields]
};
FieldTypes->forWrite(=> Map<QueryValue>) :: {
    $->mapKeyValue(^[key: String, value: Any] => QueryValue :: ?whenTypeOf(#key) {
        `DatabaseFieldName: PreparedValue[#key],
        ~: SqlValue['1']
    })
};


Ox->selectAllQuery(=> DatabaseSqlQuery) :: {
    SelectQuery[
        tableName: $ormModel.table,
        fields: $fieldTypes->forSelect[$ormModel.table],
        joins: [],
        queryFilter: SqlQueryFilter[SqlRawExpression['1']],
        orderBy: $ormModel->orderBy,
        limit: null
    ]->asDatabaseSqlQuery
};
Ox->selectOneQuery(=> DatabaseSqlQuery) :: {
    {SelectQuery[
        tableName: $ormModel.table,
        fields: $fieldTypes->forSelect[$ormModel.table],
        joins: [],
        queryFilter: $ormModel->filterByKeyField,
        orderBy: null,
        limit: null
    ]}->asDatabaseSqlQuery
};
Ox->insertQuery(=> DatabaseSqlQuery) ::
    {InsertQuery[$ormModel.table, $fieldTypes->forWrite]}
    ->asDatabaseSqlQuery;
Ox->updateQuery(=> DatabaseSqlQuery) ::
    {UpdateQuery[$ormModel.table, $fieldTypes->forWrite, $ormModel->filterByKeyField]}
    ->asDatabaseSqlQuery;
Ox->deleteQuery(=> DatabaseSqlQuery) ::
    {DeleteQuery[$ormModel.table, $ormModel->filterByKeyField]}
    ->asDatabaseSqlQuery;