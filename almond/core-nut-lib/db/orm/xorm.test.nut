test $db/orm/xorm:

Car := [brand: NonEmptyString, model: NonEmptyString, year: Integer<1880..>];
Power := Integer<0..>;

Product := [id: Integer, name: NonEmptyString, price: NonNegativeReal];
ProductModel = Type<Product>;
ProductModel ==> OrmModel :: OrmModel[table: 'products', keyField: 'id'];

==> TestCases :: {
    [
        ^ => TestResult :: TestResult[
            name: 'Test ORM Unknown Model',
            expected: @UnknownOrmModel!`Car,
            actual : ^ :: Ox[`Car]
        ],
        ^ => TestResult :: TestResult[
            name: 'Test ORM Unknown Field Types',
            expected: @UnknownOrmModel!`Power,
            actual : ^ :: Ox[`Power]
        ],
        ^ => TestResult :: TestResult[
            name: 'Test ORM Select All Query',
            expected: 'SELECT `products`.`id` AS `id`, `products`.`name` AS `name`, `products`.`price` AS `price` FROM `products` WHERE 1',
            actual : ^ :: Ox[`Product]?->selectAllQuery->as(`DatabaseSqlQuery)
        ],
        ^ => TestResult :: TestResult[
            name: 'Test ORM Select One Query',
            expected: 'SELECT `products`.`id` AS `id`, `products`.`name` AS `name`, `products`.`price` AS `price` FROM `products` WHERE `products`.`id` = :id',
            actual : ^ :: Ox[`Product]?->selectOneQuery->as(`DatabaseSqlQuery)
        ],
        ^ => TestResult :: TestResult[
            name: 'Test ORM Insert Query',
            expected: 'INSERT INTO `products` (`id`, `name`, `price`) VALUES (:id, :name, :price)',
            actual : ^ :: Ox[`Product]?->insertQuery->as(`DatabaseSqlQuery)
        ],
        ^ => TestResult :: TestResult[
            name: 'Test ORM Update Query',
            expected: 'UPDATE `products` SET `id` = :id, `name` = :name, `price` = :price WHERE `products`.`id` = :id',
            actual : ^ :: Ox[`Product]?->updateQuery->as(`DatabaseSqlQuery)
        ],
        ^ => TestResult :: TestResult[
            name: 'Test ORM Update Query',
            expected: 'DELETE FROM `products` WHERE `products`.`id` = :id',
            actual : ^ :: Ox[`Product]?->deleteQuery->as(`DatabaseSqlQuery)
        ]
    ]
};