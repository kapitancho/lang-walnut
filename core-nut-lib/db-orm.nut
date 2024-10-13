module db-orm %% db, db-sql-query-builder, db-sql-quoter-mysql:

==> DatabaseConnection :: DatabaseConnection['sqlite:db.sqlite'];

DatabaseTableName = String<1..>;
DatabaseFieldName = String<1..>;
OrmModel <: [table: DatabaseTableName, keyField: DatabaseFieldName, sequenceField: ?DatabaseFieldName];
UnknownOrmModel = $[type: Type];

gTypes = ^Any => Map<Type> :: {
    ?whenTypeOf(#) is {
        type{Type<Subtype>}: gTypes(#->baseType),
        type{Type<Record>}: #->itemTypes,
        type{Type<Alias>}: gTypes(#->aliasedType),
        type{Type<Type>}: gTypes(#->refType),
        ~: [:]
    }
};

typeToOrmModel = ^Type => Result<OrmModel, UnknownOrmModel> :: {
    ormModel = #->as(type{OrmModel});
    ?whenTypeOf(ormModel) is {
        type{OrmModel}: ormModel,
        ~: @UnknownOrmModel[#]
    }
};

getSelectAllQuery = ^Type => Result<DatabaseSqlQuery, UnknownOrmModel> :: {
    ormModel = ?noError(typeToOrmModel(#));
    t = gTypes(#);
    sf = ormModel.sequenceField;
    fields = ?whenTypeOf(t) is {
        type{Map<1..>}: t->mapKeyValue(^[key: String, value: Any] => TableField|String<1..> :: ?whenTypeOf(#key) is {
            type{DatabaseFieldName}: TableField[ormModel.table, #key],
            ~: '1'
        }),
        ~: [a: '1'] /*TODO*/
    };
    orderBy = ?whenTypeOf(sf) is {
        type{String<1..>}: SqlOrderByFields[[SqlOrderByField[sf, SqlOrderByDirection.Asc]]],
        ~: null
    };
    {SelectQuery[
        tableName: ormModel.table,
        fields: SqlSelectFieldList[fields],
        joins: [],
        queryFilter: SqlQueryFilter[SqlRawExpression['1']],
        orderBy: orderBy,
        limit: null
    ]}->asDatabaseSqlQuery
};

getSelectOneQuery = ^Type => Result<DatabaseSqlQuery, UnknownOrmModel> :: {
    ormModel = ?noError(typeToOrmModel(#));
    t = gTypes(#);
    fields = ?whenTypeOf(t) is {
        type{Map<1..>}: t->mapKeyValue(^[key: String, value: Any] => TableField|String<1..> :: ?whenTypeOf(#key) is {
            type{DatabaseFieldName}: TableField[ormModel.table, #key],
            ~: '1'
        }),
        ~: [a: '1'] /*TODO*/
    };
    {SelectQuery[
        tableName: ormModel.table,
        fields: SqlSelectFieldList[fields],
        joins: [],
        queryFilter: SqlQueryFilter[SqlFieldExpression[
            TableField[ormModel.table, ormModel.keyField], SqlFieldExpressionOperation.Equals, PreparedValue[ormModel.keyField]
        ]],
        orderBy: null,
        limit: null
    ]}->asDatabaseSqlQuery
};

getInsertQuery = ^Type => Result<DatabaseSqlQuery, UnknownOrmModel> :: {
    ormModel = ?noError(typeToOrmModel(#));
    t = gTypes(#);
    fields = ?whenTypeOf(t) is {
        type{Map<1..>}: t->mapKeyValue(^[key: String, value: Any] => QueryValue :: ?whenTypeOf(#key) is {
            type{DatabaseFieldName}: PreparedValue[#key],
            ~: SqlValue['1']
        }),
        ~: [a: SqlValue['1']] /*TODO*/
    };
    {InsertQuery[ormModel.table, fields]}->asDatabaseSqlQuery
};

Type ==> InsertQuery @ UnknownOrmModel :: {
    ormModel = ?noError(typeToOrmModel($));
    t = gTypes($);
    fields = ?whenTypeOf(t) is {
        type{Map<1..>}: t->mapKeyValue(^[key: String, value: Any] => QueryValue :: ?whenTypeOf(#key) is {
            type{DatabaseFieldName}: PreparedValue[#key],
            ~: SqlValue['1']
        }),
        ~: [a: SqlValue['1']] /*TODO*/
    };
    InsertQuery[ormModel.table, fields]
};

Type ==> DeleteQuery @ UnknownOrmModel :: {
    ormModel = ?noError(typeToOrmModel($));
    filter = SqlQueryFilter[SqlFieldExpression[
        TableField[ormModel.table, ormModel.keyField], SqlFieldExpressionOperation.Equals, PreparedValue[ormModel.keyField]
    ]];
    DeleteQuery[ormModel.table, filter]
};

getUpdateQuery = ^Type => Result<DatabaseSqlQuery, UnknownOrmModel> :: {
    ormModel = ?noError(typeToOrmModel(#));
    t = gTypes(#);
    fields = ?whenTypeOf(t) is {
        type{Map<1..>}: t->mapKeyValue(^[key: String, value: Any] => QueryValue :: ?whenTypeOf(#key) is {
            type{DatabaseFieldName}: PreparedValue[#key],
            ~: SqlValue['1']
        }),
        ~: [a: SqlValue['1']] /*TODO*/
    };
    filter = SqlQueryFilter[SqlFieldExpression[
        TableField[ormModel.table, ormModel.keyField], SqlFieldExpressionOperation.Equals, PreparedValue[ormModel.keyField]
    ]];
    {UpdateQuery[ormModel.table, fields, filter]}->asDatabaseSqlQuery
};

Type ==> UpdateQuery @ UnknownOrmModel :: {
    ormModel = ?noError(typeToOrmModel($));
    t = gTypes($);
    fields = ?whenTypeOf(t) is {
        type{Map<1..>}: t->mapKeyValue(^[key: String, value: Any] => QueryValue :: ?whenTypeOf(#key) is {
            type{DatabaseFieldName}: PreparedValue[#key],
            ~: SqlValue['1']
        }),
        ~: [a: SqlValue['1']] /*TODO*/
    };
    filter = SqlQueryFilter[SqlFieldExpression[
        TableField[ormModel.table, ormModel.keyField], SqlFieldExpressionOperation.Equals, PreparedValue[ormModel.keyField]
    ]];
    UpdateQuery[ormModel.table, fields, filter]
};
