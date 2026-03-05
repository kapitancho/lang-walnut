test $db/sql/query-builder %% $db/sql/quoter-mysql:

==> TestCases :: {
    [
        ^ => TestResult :: TestResult[
            name: 'Test Insert Query Builder',
            expected: 'INSERT INTO `products` (`name`, `price`) VALUES (:name, 9.99)',
            actual : ^ :: {
                InsertQuery[
                    tableName: 'products',
                    values: [name: PreparedValue['name'], price: SqlValue[9.99]]
                ]
            }->as(`DatabaseSqlQuery)
        ],
        ^ => TestResult :: TestResult[
            name: 'Test Update Query Builder',
            expected: 'UPDATE `products` SET `name` = :name, `price` = 9.99 WHERE id = 10',
            actual : ^ :: {
                UpdateQuery[
                    tableName: 'products',
                    values: [name: PreparedValue['name'], price: SqlValue[9.99]],
                    queryFilter: SqlQueryFilter[
                        SqlRawExpression['id = 10']
                    ]
                ]
            }->as(`DatabaseSqlQuery)
        ],
        ^ => TestResult :: TestResult[
            name: 'Test Delete Query Builder',
            expected: 'DELETE FROM `products` WHERE id = 10',
            actual : ^ :: {
                DeleteQuery[
                    tableName: 'products',
                    queryFilter: SqlQueryFilter[
                        SqlRawExpression['id = 10']
                    ]
                ]
            }->as(`DatabaseSqlQuery)
        ],
        ^ => TestResult :: TestResult[
            name: 'Test Select Query Builder - basic',
            expected: 'SELECT `name` AS `name`, `products`.`price` AS `price`, 1 AS `step` FROM `products` WHERE id = 10',
            actual : ^ :: {
                SelectQuery[
                    tableName: 'products',
                    fields: SqlSelectFieldList[
                        fields: [
                            name: 'name',
                            price: TableField['products', 'price'],
                            step: SqlValue[1]
                        ]
                    ],
                    joins: [],
                    queryFilter: SqlQueryFilter[
                        SqlRawExpression['id = 10']
                    ],
                    orderBy: null,
                    limit: null
                ]
            }->as(`DatabaseSqlQuery)
        ],
        ^ => TestResult :: TestResult[
            name: 'Test Select Query Builder - full',
            expected: 'SELECT `name` AS `name`, `products`.`price` AS `price`, 1 AS `step` FROM `products` JOIN `categories` AS `c` ON products.category_id = c.id LEFT JOIN `suppliers` AS `s` ON products.supplier_id = s.id WHERE id = 10 ORDER BY `name` ASC, `price` DESC LIMIT 4 OFFSET 3',
            actual : ^ :: {
                SelectQuery[
                    tableName: 'products',
                    fields: SqlSelectFieldList[
                        fields: [
                            name: 'name',
                            price: TableField['products', 'price'],
                            step: SqlValue[1]
                        ]
                    ],
                    joins: [
                        SqlTableJoin[tableAlias: 'c', tableName: 'categories', joinType: SqlTableJoinType.Inner, queryFilter: SqlQueryFilter[SqlRawExpression['products.category_id = c.id']]],
                        SqlTableJoin[tableAlias: 's', tableName: 'suppliers', joinType: SqlTableJoinType.Left, queryFilter: SqlQueryFilter[SqlRawExpression['products.supplier_id = s.id']]]
                    ],
                    queryFilter: SqlQueryFilter[
                        SqlRawExpression['id = 10']
                    ],
                    orderBy: SqlOrderByFields[fields: [
                        SqlOrderByField[field: 'name', direction: SqlOrderByDirection.Asc],
                        SqlOrderByField[field: 'price', direction: SqlOrderByDirection.Desc]
                    ]],
                    limit: SqlSelectLimit[limit: 4, offset: 3]
                ]
            }->as(`DatabaseSqlQuery)
        ]
    ]
};