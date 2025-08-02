test $db/orm/xorm-repository:

==> DatabaseConnection :: DatabaseConnection![dsn: 'sqlite::memory:'];

Car := [brand: NonEmptyString, model: NonEmptyString, year: Integer<1880..>];

Product := [id: Integer, name: NonEmptyString, price: NonNegativeReal];
ProductModel = Type<Product>;
ProductModel ==> OrmModel :: OrmModel[table: 'products', keyField: 'id'];

==> TestCases :: {
    beforeFn = ^ %% [c: DatabaseConnector] :: {
        %c->execute[
            query: 'CREATE TABLE IF NOT EXISTS `products` (`id` INTEGER PRIMARY KEY, `name` TEXT NOT NULL, `price` REAL NOT NULL)',
            boundParameters: []
        ] *> ('Failed to create products table');

        %c->execute[
            query: 'INSERT INTO `products` (`id`, `name`, `price`) VALUES (1, "Product 1", 10.0), (2, "Product 2", 20.0)',
            boundParameters: []
        ] *> ('Failed to insert initial products');
    };

    afterFn = ^ %% [c: DatabaseConnector] :: {
        %c->execute[
            query: 'DROP TABLE IF EXISTS `products`',
            boundParameters: []
        ] *> ('Failed to drop products table');
    };

    [
        ^ => TestResult :: TestResult[
            name: 'Test ORM Unknown Model',
            expected: `Error<ExternalError>,
            actual : ^ :: {OxRepository[type: `Car, model: `Car]}->type
        ],
        ^ => TestResult :: TestResult[
            name: 'Test get all products',
            expected: [
                Product![id: 1, name: 'Product 1', price: 10],
                Product![id: 2, name: 'Product 2', price: 20]
            ],
            actual : ^ :: ?noError(OxRepository[type: `Product, model: `Product])->all,
            before: beforeFn,
            after: afterFn
        ],
        ^ => TestResult :: TestResult[
            name: 'Test get product by id',
            expected: Product![id: 1, name: 'Product 1', price: 10],
            actual : ^ :: ?noError(OxRepository[type: `Product, model: `Product])->one(1),
            before: beforeFn,
            after: afterFn
        ],
        ^ => TestResult :: TestResult[
            name: 'Test get product by id not found',
            expected: @EntryNotFound[key: 3],
            actual : ^ :: ?noError(OxRepository[type: `Product, model: `Product])->one(3),
            before: beforeFn,
            after: afterFn
        ],
        ^ => TestResult :: TestResult[
            name: 'Test insert product',
            expected: null,
            actual : ^ :: ?noError(OxRepository[type: `Product, model: `Product])->insertOne(
                Product![id: 3, name: 'Product 3', price: 30]
            ),
            before: beforeFn,
            after: afterFn
        ],
        ^ => TestResult :: TestResult[
            name: 'Test insert product duplicate id',
            expected: @DuplicateEntry[key: 1],
            actual : ^ :: {?noError(OxRepository[type: `Product, model: `Product])->insertOne(
                Product![id: 1, name: 'Product 1', price: 10]
            )},
            before: beforeFn,
            after: afterFn
        ],
        ^ => TestResult :: TestResult[
            name: 'Test update product',
            expected: null,
            actual : ^ :: ?noError(OxRepository[type: `Product, model: `Product])->updateOne(
                Product![id: 1, name: 'Updated Product 1', price: 15]
            ),
            before: beforeFn,
            after: afterFn
        ],
        ^ => TestResult :: TestResult[
            name: 'Test update product not found',
            expected: @EntryNotFound[key: 999],
            actual : ^ :: ?noError(OxRepository[type: `Product, model: `Product])->updateOne(
                Product![id: 999, name: 'Nonexistent Product', price: 100]
            ),
            before: beforeFn,
            after: afterFn
        ],
        ^ => TestResult :: TestResult[
            name: 'Test delete product',
            expected: null,
            actual : ^ :: ?noError(OxRepository[type: `Product, model: `Product])->deleteOne(1),
            before: beforeFn,
            after: afterFn
        ],
        ^ => TestResult :: TestResult[
            name: 'Test delete product not found',
            expected: @EntryNotFound[key: 999],
            actual : ^ :: ?noError(OxRepository[type: `Product, model: `Product])->deleteOne(999)
        ]
    ]
};