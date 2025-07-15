test $db/sql/query-builder %% $db/sql/quoter-mysql:

==> DatabaseConnection :: DatabaseConnection![dsn: 'sqlite::memory:'];

==> TestCases :: {
    [
        ^ => TestResult :: TestResult[
            name: 'Test Insert Query Builder',
            expected: 'INSERT INTO products (`name`, `price`) VALUES (:name, 9.99)',
            actual = ^ :: {
                InsertQuery[
                    tableName: 'products',
                    values: [name: PreparedValue['name'], price: SqlValue[9.99]]
                ]
            }->as(`DatabaseSqlQuery)
        ],
        ^ => TestResult :: TestResult[
            name: 'Test Update Query Builder',
            expected: 'UPDATE products SET `name` = :name, `price` = 9.99 WHERE id = 10',
            actual = ^ :: {
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
            expected: 'DELETE FROM products WHERE id = 10',
            actual = ^ :: {
                DeleteQuery[
                    tableName: 'products',
                    queryFilter: SqlQueryFilter[
                        SqlRawExpression['id = 10']
                    ]
                ]
            }->as(`DatabaseSqlQuery)
        ],
        ^ => TestResult :: TestResult[
            name: 'Test Select Query Builder',
            expected: 'SELECT `name` AS `name`, `products`.`price` AS `price`, 1 AS `step` FROM `products` WHERE id = 10',
            actual = ^ :: {
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
        ]
    ]
};