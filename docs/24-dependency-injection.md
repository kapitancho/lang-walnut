# Dependency Injection

## Overview

Walnut features a built-in compile-time dependency injection (DI) system that enables loose coupling, testability, and clean architecture. Dependencies are declared explicitly in function signatures and resolved automatically at compile-time through a dependency graph.

The DI system is type-safe, supports dependency chains, and integrates seamlessly with functions, methods, constructors, and cast functions. Unlike runtime DI frameworks, Walnut's DI is resolved during compilation, ensuring that all dependencies are satisfied before the program runs.

## Key Concepts

### Dependency Declaration

Dependencies are declared using the `%%` operator in function-like constructs:

- **Functions**: `^ParamType => ReturnType %% DepType :: body`
- **Methods**: `Type->method(^ParamType => ReturnType) %% DepType :: body`
- **Constructors**: `Type[ParamType] %% DepType :: body`
- **Cast Functions**: `FromType ==> ToType %% DepType :: body`

### Dependency Variable

Within the body, dependencies are accessed via the `%` variable:

- `%` - access the dependency value
- `%field` - auto-extracted fields from record dependencies
- '%index' - auto-extracted fields from tuple dependencies
- Named dependencies: `d: DepType` creates a `d` variable, ~DepType creates a `depType` variable

### Dependency Resolution

When code requiring dependencies is executed:

1. **Compile-time graph building** - Walnut analyzes all dependency relationships
2. **Provider lookup** - Finds cast functions that produce required types: `==> TypeName :: value`
3. **Error on failure** - Compilation fails if dependencies cannot be resolved
4. **Automatic injection** - Dependencies are curried into functions automatically
5. **Tuple and record support** - Dependencies can be tuples or records of multiple types. Each field is recursively resolved.

## Dependency Declaration Syntax

### In Functions

Functions can declare dependencies that will be automatically injected:

```walnut
/* Basic dependency syntax */
getCurrentTime = ^=> Integer %% ~Clock :: clock->now;

/* Multiple dependencies */
saveData = ^data: String => *Null %% [~Database, ~Logger] :: {
    %logger->log('Saving data');
    %database->save(data)
};

/* Single dependency type */
processUser = ^user: User => *Result<UserId, String> %% db: Database :: {
    db->saveUser(user)
};
```

**Dependency types:**
- **Single type**: `%% TypeName` - access via `%`
- **Record of types**: `%% [field1: Type1, field2: Type2]` - access via `%field1`, `%field2`
- **Type-named shorthand**: `%% ~TypeName` - creates field `typeName: TypeName` (lowercase)
- **Multiple type-named**: `%% [~Database, ~Logger]` - creates `%database`, `%logger`

### In Methods

Methods support the same dependency syntax:

```walnut
Person := $[name: String<1..>, age: Integer<0..>];

/* Method with dependency (no parameter) */
Person->greet(=> String) %% [~CommunicationProtocol] ::
    ['Hello, ', %communicationProtocol->prefix, $name]->combineAsString('');

/* Method with parameter and dependency */
Person->message(^String => String) %% CommunicationProtocol ::
    [%->prefix, $name, ', ', #]->combineAsString('');
```

**Example from demo-method.nut:**

```walnut
module demo-method:

/* Define dependency type */
CommunicationProtocol := $[prefix: String];
CommunicationProtocol->prefix(=> String) :: $prefix;

/* Provide DI instance */
==> CommunicationProtocol :: CommunicationProtocol['Dear '];

/* Main type */
Person := $[name: String<1..>, age: Integer<0..>];

/* Method with record dependency */
Person->greet(=> String) %% [~CommunicationProtocol] ::
    ['Hello, ', %communicationProtocol->prefix, $name]->combineAsString('');

/* Method with single dependency */
Person->message(^String => String) %% CommunicationProtocol ::
    [%->prefix, $name, ', ', #]->combineAsString('');

/* Usage */
person = Person['Alice', 27];
person->greet;  /* Dependencies injected automatically */
person->message('How are you?');
```

### In Constructors

Constructors can use dependencies during object creation:

```walnut
/* From demo-all.nut */
MyAtom := ();
MySealed := $[a: Integer, b: Integer];

/* Constructor with dependency */
MySealed[a: Real, b: Real] %% MyAtom :: [a: #a->asInteger, b: #b->asInteger];

/* Usage */
obj = MySealed[3.7, -2.3];  /* MyAtom dependency injected */
```

**With validation:**

```walnut
ValidatedType := $[x: Integer, y: Integer];

ValidatedType[a: Integer, b: Integer] @ ValidationError %% ~Validator :: {
    %validator->check([a: #a, b: #b]);
    [x: #a, y: #b]
};
```

### In Cast Functions

Cast functions support dependencies for conversion logic:

```walnut
/* From demo-money.nut */
ExchangeRateProvider = ^[fromCurrency: Currency, toCurrency: Currency] => *Real;
MoneyCurrencyConvertor = ^[money: Money, toCurrency: Currency] => *Money;

/* Cast implementation with dependency */
==> MoneyCurrencyConvertor %% [~ExchangeRateProvider] ::
    ^[money: Money, toCurrency: Currency] => *Money :: {
        rate = %exchangeRateProvider => invoke [#money.currency, #toCurrency];
        amount = #money.amount * rate;
        Money[#toCurrency, amount]
    };
```

**SQL query builder example:**

```walnut
/* From core-nut-lib/db/sql/query-builder.nut */
SqlValue ==> SqlString %% ~SqlQuoter ::
    sqlQuoter.quoteValue($value);

InsertQuery ==> DatabaseSqlQuery %% ~SqlQuoter ::
    'INSERT INTO ' + {%sqlQuoter->quoteIdentifier($tableName)} + /* ... */;
```

## Accessing Dependencies

### The `%` Variable

The `%` variable provides access to injected dependencies:

```walnut
/* Single dependency - access via % */
getCurrentTime = ^=> Integer %% Clock :: %->now;

/* Named dependency - access via %.fieldName */
logMessage = ^msg: String => Null %% [logger: Logger] ::
    %.logger->log(msg);
```

### Auto-Extracted Fields (`%field`)

For record-based and tuple-based dependencies, fields are automatically extracted:

```walnut
/* Record dependency */
Config := $[host: String, port: Integer];

connectToServer = ^=> String %% Config ::
    %host + ':' + %port->asString;  /* Direct access to %host, %port */

/* Multiple dependencies */
processRequest = ^data: String => *Result<Response, Error> %% [~Database, ~Logger] :: {
    %logger->log('Processing request');  /* Access via %logger */
    %database->save(data)                 /* Access via %database */
};
```

**Type-named dependencies** use `~TypeName` syntax:
- `~Database` creates field `database: Database`
- `~Logger` creates field `logger: Logger`
- Access via `%database`, `%logger`

### Named Dependencies

Explicitly name dependencies for clarity:

```walnut
/* Explicit field names */
authenticate = ^credentials: Credentials => *Result<Token, AuthError>
    %% [db: Database, auth: AuthService] :: {
    user = %db->findUser(credentials.username);
    %auth->validatePassword(user, credentials.password)
};

/* Type-named (shorthand) */
authenticate = ^credentials: Credentials => *Result<Token, AuthError>
    %% [~Database, ~AuthService] :: {
    user = %database->findUser(credentials.username);
    %authService->validatePassword(user, credentials.password)
};
```

## Dependency Resolution

### Compile-Time Dependency Graph

Walnut builds a dependency graph at compile time:

1. **Scan all dependency declarations** in functions, methods, constructors, casts
2. **Identify providers** - cast functions of form `==> TypeName`
3. **Build resolution graph** - connect dependencies to providers
4. **Detect cycles** - fail compilation if circular dependencies exist
5. **Check completeness** - fail if any dependency cannot be resolved

### Provider Cast Functions

Dependencies are provided through special cast functions:

**Basic syntax:** `==> TypeName :: value`

**Full form:** `DependencyContainer ==> TypeName :: value`

**With dependencies:** `==> TypeName %% OtherDeps :: body`

```walnut
/* Simple provider - no dependencies */
CommunicationProtocol := $[prefix: String];
==> CommunicationProtocol :: CommunicationProtocol['Dear '];

/* Provider with dependencies */
ExchangeRateProvider = ^[fromCurrency: Currency, toCurrency: Currency] => *Real;

/* Mock implementation */
==> ExchangeRateProvider :: ^[fromCurrency: Currency, toCurrency: Currency] => *Real ::
    ?whenValueOf(#fromCurrency) is {
        Currency.Euro: ?whenValueOf(#toCurrency) is {
            Currency.Dollar: 1.2,
            Currency.Yen: 130.0
        }
    };
```

### Error Handling

Compilation fails with descriptive errors when dependencies cannot be resolved:

```walnut
/* Error types defined in core.nut */
DependencyContainerErrorType := (
    CircularDependency,
    Ambiguous,
    NotFound,
    UnsupportedType,
    ErrorWhileCreatingValue
);

DependencyContainerError := [
    targetType: Type,
    errorOnType: Type,
    errorMessage: String,
    errorType: DependencyContainerErrorType
];
```

**Error scenarios:**

```walnut
/* Circular dependency */
A := #Integer;
B := #A;
==> A %% B :: A(42);
==> B %% A :: B(%);

f = ^ %% B :: null;  /* Error: circular dependency A -> B -> A */

/* Missing provider */
DatabaseConfig := $[host: String];

connectToDb = ^=> Connection %% DatabaseConfig :: /* ... */;
/* Error: DatabaseConfig cannot be resolved (no provider defined) */

/* Ambiguous provider */
==> Logger :: ConsoleLogger();
==> Logger :: FileLogger();
/* Error: multiple providers for Logger (ambiguous) */
```

### DependencyContainer Type

The `DependencyContainer` type is a special compile-time construct:

```walnut
/* Defined in core.nut */
DependencyContainer := ();
```

**Used implicitly in provider casts:**

```walnut
/* These are equivalent: */
==> TypeName :: value
DependencyContainer ==> TypeName :: value

/* With dependencies: */
==> TypeName %% Deps :: body
DependencyContainer ==> TypeName %% Deps :: body
```

**The valueOf method:**

```walnut
/* Conceptual signature (not directly callable) */
DependencyContainer->valueOf(^Type<T> => T);

/* Used internally during dependency resolution */
```

## Providing Dependencies

### Basic Providers

Simple providers return constant values or create new instances:

```walnut
/* Constant provider */
AppName := #String;
==> AppName :: AppName('MyApplication');

/* Factory provider */
ShoppingCart := $[items: Mutable<Map<ShoppingCartItem>>];
==> ShoppingCart :: ShoppingCart();  /* No-argument constructor */

/* Configured provider */
Config := $[host: String, port: Integer, debug: Boolean];
==> Config :: Config[host: 'localhost', port: 8080, debug: true];
```

### Providers with Dependencies

Providers can depend on other types, creating dependency chains:

```walnut
/* From demo-money.nut */
ExchangeRateProvider = ^[fromCurrency: Currency, toCurrency: Currency] => *Real;
MoneyCurrencyConvertor = ^[money: Money, toCurrency: Currency] => *Money;

/* Provider depends on ExchangeRateProvider */
==> MoneyCurrencyConvertor %% [~ExchangeRateProvider] ::
    ^[money: Money, toCurrency: Currency] => *Money :: {
        rate = %exchangeRateProvider => invoke [#money.currency, #toCurrency];
        amount = #money.amount * rate;
        Money[#toCurrency, amount]
    };

/* Base provider (no dependencies) */
==> ExchangeRateProvider :: ^[fromCurrency: Currency, toCurrency: Currency] => *Real ::
    /* mock implementation */;
```

### Dependency Chains

Dependencies can form chains, resolved recursively:

```walnut
/* Layer 1: Base service */
Database := ();
==> Database :: Database();

/* Layer 2: Repository depends on Database */
UserRepository := ();
==> UserRepository %% ~Database ::
    UserRepository[db: %database];

/* Layer 3: Service depends on Repository */
UserService := ();
==> UserService %% ~UserRepository ::
    UserService[repo: %userRepository];

/* Layer 4: Controller depends on Service */
UserController := ();
==> UserController %% ~UserService ::
    UserController[service: %userService];

/* All dependencies resolved automatically */
/* UserController -> UserService -> UserRepository -> Database */
```

### Short dependency syntax

Sometimes a dependency can be defined by using another one:

```walnut
/* Given that we have */
T ==> U :: /* some implementation */;
==> T :: /* some other implementation */;

/* Then */ 
==> U %% T; 
```

is equivalent to
```walnut
==> U %% T :: %->as(`U); 
```

### Complex Provider Example

From `core-nut-lib/http/bundle/composite-router.nut`:

```walnut
==> HttpCompositeRequestHandler %% [
    notFoundHandler: HttpNotFoundHandler,
    indexHandler: IndexHandler,
    aboutHandler: AboutHandler,
    specialHandler: SpecialHandler
] :: {
    HttpCompositeRequestHandler[
        handlers: [
            HttpRequestRoute[pattern: '/', handler: %indexHandler],
            HttpRequestRoute[pattern: '/about', handler: %aboutHandler],
            HttpRequestRoute[pattern: '/special', handler: %specialHandler]
        ],
        fallbackHandler: %notFoundHandler
    ]
};
```

## Automatic Injection

### Function Currying

Dependencies are automatically curried into functions:

```walnut
/* Function with dependency */
getCurrentTime = ^=> Integer %% Clock :: %->now;

/* When called, Clock is injected automatically */
timestamp = getCurrentTime();  /* No need to pass Clock manually */

/* Conceptually equivalent to: */
/* getCurrentTime_internal = ^clock: Clock => Integer :: clock->now; */
/* getCurrentTime = getCurrentTime_internal(injectedClockInstance); */
```

### Method Dependency Injection

Methods with dependencies work similarly:

```walnut
Person := $[name: String];

Person->greet(=> String) %% CommunicationProtocol ::
    %->prefix + $name;

/* When called, CommunicationProtocol is injected */
person = Person['Alice'];
greeting = person->greet;  /* CommunicationProtocol injected automatically */
```

### Module-Level Dependencies

Modules can declare dependencies at the top level:

```walnut
module shopping-cart/cart-test %% $test/runner, shopping-cart/model:

/* Module depends on test/runner and shopping-cart/model */
/* All exports from those modules are available */
```

## Real-World Examples

### Example 1: Communication Protocol (from demo-method.nut)

```walnut
module demo-method:

/* Define dependency type */
CommunicationProtocol := $[prefix: String];
CommunicationProtocol->prefix(=> String) :: $prefix;

/* Provide DI instance */
==> CommunicationProtocol :: CommunicationProtocol['Dear '];

/* Main type */
Person := $[name: String<1..>, age: Integer<0..>];

/* Method with dependency - no parameter */
Person->greet(=> String) %% [~CommunicationProtocol] ::
    ['Hello, ', %communicationProtocol->prefix, $name]->combineAsString('');

/* Method with parameter and dependency */
Person->message(^String => String) %% CommunicationProtocol ::
    [%->prefix, $name, ', ', #]->combineAsString('');

/* Method with no types specified */
Person->age() :: $age;

/* Method with parameter, no return type */
Person->askQuestion(^String) :: ?whenValueOf(#) is {
    'How old are you?': $age,
    'What is your name?': $name,
    ~: 'I do not understand the question'
};

/* Usage */
person = Person['Alice', 27];
person->greet;  /* "Hello, Dear Alice" */
person->message('How are you?');  /* "Dear Alice, How are you?" */
```

### Example 2: Money and Exchange Rates (from demo-money.nut)

```walnut
module demo-money:

/* Types */
Amount = Real;
Currency := (Euro, Dollar, Yen);

Currency ==> String :: ?whenValueOf($) is {
    Currency.Euro: '€',
    Currency.Dollar: '$',
    Currency.Yen: '¥'
};

Money := #[~Currency, ~Amount];
Money ==> String :: {$currency->asString} + $amount->asString;

/* Dependency types */
ExchangeRateProvider = ^[fromCurrency: Currency, toCurrency: Currency] => *Real;
MoneyCurrencyConvertor = ^[money: Money, toCurrency: Currency] => *Money;

/* Provider implementation with dependency */
==> MoneyCurrencyConvertor %% [~ExchangeRateProvider] ::
    ^[money: Money, toCurrency: Currency] => *Money :: {
        rate = %exchangeRateProvider => invoke [#money.currency, #toCurrency];
        amount = #money.amount * rate;
        Money[#toCurrency, amount]
    };

/* Mock exchange rate provider */
==> ExchangeRateProvider :: ^[fromCurrency: Currency, toCurrency: Currency] => *Real ::
    ?whenValueOf(#fromCurrency) is {
        Currency.Euro: ?whenValueOf(#toCurrency) is {
            Currency.Euro: 1,
            Currency.Dollar: 1.2,
            Currency.Yen: 130.0
        },
        Currency.Dollar: ?whenValueOf(#toCurrency) is {
            Currency.Euro: 0.8,
            Currency.Dollar: 1,
            Currency.Yen: 105.0
        }
    };

/* Binary operators with dependencies */
Money->binaryPlus(^Money => *Money) %% [~MoneyCurrencyConvertor] :: {
    m = %moneyCurrencyConvertor => invoke [#, $currency];
    Money[$currency, $amount + m.amount]
};

Money->binaryMinus(^Money => *Money) %% [~MoneyCurrencyConvertor] :: {
    m = %moneyCurrencyConvertor => invoke [#, $currency];
    Money[$currency, $amount - m.amount]
};

Money->binaryMultiply(^Real => Money) ::
    Money[$currency, $amount * #];

/* Method with dependency */
Money->toCurrency(^Currency => *Money) %% [~MoneyCurrencyConvertor] ::
    %moneyCurrencyConvertor => invoke [$, #];

/* Usage */
myEuro = Money[Currency.Euro, 100];
myDollar = Money[Currency.Dollar, 70];
myTotal = myEuro + myDollar;  /* Dependencies injected */
myEuroInYen = myEuro->toCurrency(Currency.Yen);
```

### Example 3: Shopping Cart (from shopping-cart/model.nut)

```walnut
module shopping-cart/model:

/* Types */
ProductId := String<1..>;
ProductTitle := String<1..>;
ProductPrice := Real<0..>;
ShoppingCartQuantity := Integer<1..>;

ShoppingCartProduct = [id: ProductId, title: String, price: ProductPrice];
ShoppingCartItem = [product: Shape<ShoppingCartProduct>, quantity: Mutable<ShoppingCartQuantity>];

ShoppingCart := $[items: Mutable<Map<ShoppingCartItem>>];

/* No-argument constructor */
ShoppingCart() :: [items: mutable{Map<ShoppingCartItem>, [:]}];

/* Product types */
ShirtSize := (S, M, L, XL, XXL);
ShirtColor := (Red, Green, Blue, Black, White);
Shirt := $[id: ProductId, model: ProductTitle, price: ProductPrice, size: ShirtSize, color: ShirtColor];

/* Cast to common product interface */
Shirt ==> ShoppingCartProduct :: [
    id: $id,
    title: $model->asString + ' ' + {$color->asString} + ' ' + {$size->asString},
    price: $price
];

MonitorResolution := (HD, FullHD, UHD);
MonitorSize := Real<1..>;
Monitor := $[id: ProductId, brand: BrandName, model: ProductTitle, price: ProductPrice, resolution: MonitorResolution, sizeInInches: MonitorSize];

Monitor ==> ShoppingCartProduct :: [
    id: $id,
    title: {$brand->asString} + ' ' + {$model->asString} + ' ' + {$resolution->asString} + ' ' + $sizeInInches->value->asString + '"',
    price: $price
];

/* Usage */
shirt = Shirt[id: ProductId!'s1', model: ProductTitle!'T-Shirt', price: ProductPrice!19.99, size: ShirtSize.M, color: ShirtColor.Blue];
cart = ShoppingCart();
cart->addItem[shirt->asShoppingCartProduct, ShoppingCartQuantity!1];
```

### Example 4: SQL Query Builder (from core-nut-lib/db/sql/query-builder.nut)

```walnut
/* Dependency type */
SqlQuoter := $[
    quoteIdentifier: ^String => String,
    quoteValue: ^Any => String
];

/* Cast functions with dependency */
SqlValue ==> SqlString %% ~SqlQuoter ::
    sqlQuoter.quoteValue($value);

TableField ==> SqlString %% ~SqlQuoter ::
    %sqlQuoter.quoteIdentifier($tableName) + '.' + %sqlQuoter.quoteIdentifier($fieldName);

InsertQuery ==> DatabaseSqlQuery %% ~SqlQuoter ::
    'INSERT INTO '
    + {%sqlQuoter->quoteIdentifier($tableName)}
    + ' (' + {$fields->keys->map(%sqlQuoter.quoteIdentifier)->combineAsString(', ')} + ')'
    + ' VALUES (' + {$fields->values->map(%sqlQuoter.quoteValue)->combineAsString(', ')} + ')';

UpdateQuery ==> DatabaseSqlQuery %% ~SqlQuoter ::
    'UPDATE ' + {%sqlQuoter->quoteIdentifier($tableName)}
    + ' SET ' + {$fields->mapKeyValue(^[key: String, value: Any] => String ::
        %sqlQuoter.quoteIdentifier(key) + ' = ' + %sqlQuoter.quoteValue(value)
    )->values->combineAsString(', ')};

DeleteQuery ==> DatabaseSqlQuery %% ~SqlQuoter ::
    'DELETE FROM ' + {%sqlQuoter->quoteIdentifier($tableName)};
```

### Example 5: Clock Dependency

```walnut
/* From core-nut-lib/datetime.nut */
Clock := ();

/* Function using Clock dependency */
getCurrentTimestamp = ^=> Integer %% ~Clock ::
    %clock->now;

/* Method using Clock dependency */
LogEntry := $[message: String, timestamp: Integer];

LogEntry() %% ~Clock ::
    LogEntry[message: '', timestamp: %clock->now];

/* Provider for testing */
==> Clock :: MockClock[fixedTime: 1234567890];

/* Provider for production */
==> Clock :: SystemClock();
```

### Example 6: HTTP Request Handler (from core-nut-lib/http/all.test.nut)

```walnut
/* Error handling with dependencies */
DependencyContainerError ==> HttpResponse %% ~HttpResponseBuilder ::
    %httpResponseBuilder->error(500, $errorMessage);

/* Request handler with dependencies */
IndexHandler ==> HttpRequestHandler %% ~HttpResponseBuilder ::
    ^request: {HttpRequest} => {HttpResponse} :: {
        %httpResponseBuilder->ok('Welcome to the home page')
    };

SpecialHandler ==> HttpRequestHandler %% [~HttpResponseBuilder, ~SpecialService] ::
    ^request: {HttpRequest} => {HttpResponse} :: {
        result = %specialService->process(request);
        ?whenIsError(result) {
            %httpResponseBuilder->error(500, 'Service error')
        } ~ {
            %httpResponseBuilder->ok(result)
        }
    };

/* Composite handler depends on multiple handlers */
==> HttpCompositeRequestHandler %% [
    notFoundHandler: HttpNotFoundHandler,
    indexHandler: IndexHandler,
    aboutHandler: AboutHandler,
    specialHandler: SpecialHandler
] :: {
    HttpCompositeRequestHandler[
        handlers: [
            HttpRequestRoute[pattern: '/', handler: %indexHandler],
            HttpRequestRoute[pattern: '/about', handler: %aboutHandler],
            HttpRequestRoute[pattern: '/special', handler: %specialHandler]
        ],
        fallbackHandler: %notFoundHandler
    ]
};
```

## Best Practices

### 1. Designing for DI

**Define clear interfaces:**

```walnut
/* Good - clear interface type */
Logger := $[
    log: ^String => Null,
    error: ^String => Null,
    debug: ^String => Null
];

/* Avoid - using Any everywhere */
Logger = Any;
```

**Use dependency types, not concrete implementations:**

```walnut
/* Good - depend on interface */
processData = ^data: String => *Result<Response, Error> %% ~Logger :: {
    %logger->log('Processing');
    /* ... */
};

/* Avoid - depending on concrete type */
processData = ^data: String => *Result<Response, Error> %% ~ConsoleLogger :: {
    %consoleLogger->log('Processing');
    /* ... */
};
```

**Keep dependencies explicit:**

```walnut
/* Good - dependencies are visible */
authenticate = ^credentials: Credentials => *Result<Token, Error>
    %% [~Database, ~AuthService] :: /* ... */;

/* Avoid - hidden global state */
authenticate = ^credentials: Credentials => *Result<Token, Error> :: {
    db = globalDatabase;  /* Hidden dependency */
    /* ... */
};
```

### 2. Testing with DI

**Provide test doubles:**

```walnut
/* Production provider */
==> Database :: ProductionDatabase[host: 'prod.example.com'];

/* Test provider (in test module) */
==> Database :: MockDatabase[inMemory: true];

/* Test code uses the same interface */
testUserCreation = ^=> TestResult %% ~Database :: {
    user = createUser('test');
    savedUser = %database->find(user.id);
    TestResult['User saved correctly', user, ^ :: savedUser]
};
```

**Inject dependencies at module boundaries:**

```walnut
module shopping-cart/cart-test %% $test/runner, shopping-cart/model:

/* Test provides its own implementations */
==> ShoppingCart :: ShoppingCart();
==> Shirt :: Shirt[/* test data */];

/* Tests run with test dependencies */
==> TestCases :: {
    [
        ^ => TestResult :: {
            cart = ShoppingCart();  /* Uses test provider */
            TestResult['Test name', expected, ^ :: actual]
        }
    ]
};
```

**Isolate external dependencies:**

```walnut
/* Interface for external service */
ExchangeRateProvider = ^[fromCurrency: Currency, toCurrency: Currency] => *Real;

/* Production: real API */
==> ExchangeRateProvider :: ^[from: Currency, to: Currency] => *Real :: {
    /* Make HTTP request to exchange rate API */
};

/* Test: mock data */
==> ExchangeRateProvider :: ^[from: Currency, to: Currency] => *Real :: {
    /* Return fixed test rates */
    1.2
};
```

### 3. Avoiding Dependency Hell

**Limit dependency chains:**

```walnut
/* Good - 2-3 levels deep */
Controller %% Service %% Repository %% Database

/* Avoid - too many levels */
A %% B %% C %% D %% E %% F %% G  /* Too deep */
```

**Use aggregate dependencies:**

```walnut
/* Good - bundle related dependencies */
AppServices := $[
    database: Database,
    logger: Logger,
    config: Config
];

==> AppServices %% [~Database, ~Logger, ~Config] ::
    AppServices[
        database: %database,
        logger: %logger,
        config: %config
    ];

processRequest = ^req: Request => *Response %% ~AppServices :: {
    %appServices.logger->log('Processing');
    /* ... */
};

/* Avoid - many individual dependencies */
processRequest = ^req: Request => *Response
    %% [~Database, ~Logger, ~Config, ~Cache, ~Metrics, ~Auth, ~...] :: {
    /* Too many dependencies */
};
```

**Avoid circular dependencies:**

```walnut
/* Bad - circular dependency (compilation error) */
A := #Integer;
B := #A;
==> A %% B :: A(42);
==> B %% A :: B(%);

/* Good - break the cycle */
A := #Integer;
B := #A;
C := #Integer;
==> A %% C :: A(42);
==> B %% A :: B(%);
==> C :: C(10);
```

**Keep providers simple:**

```walnut
/* Good - simple provider */
==> Logger :: ConsoleLogger();

/* Avoid - complex provider logic */
==> Logger %% [~Config, ~Database, ~Cache, ~Metrics] :: {
    /* Too much logic in provider */
    config = %config->load;
    db = %database->connect;
    /* many more lines */
    Logger[/* complex initialization */]
};
```

### 4. Organizing Dependencies

**Group by layer:**

```walnut
/* Data layer */
==> Database :: /* ... */;
==> Cache :: /* ... */;

/* Business layer */
==> UserService %% [~Database, ~Cache] :: /* ... */;
==> OrderService %% [~Database, ~Cache] :: /* ... */;

/* Presentation layer */
==> UserController %% ~UserService :: /* ... */;
==> OrderController %% ~OrderService :: /* ... */;
```

**Use module dependencies:**

```walnut
module app/controllers %% app/services, app/repositories:

/* All services and repositories available */
/* Dependencies resolved automatically */
```

### 5. Common Patterns

**Factory pattern with DI:**

```walnut
UserFactory = ^[name: String, email: String] => *User;

==> UserFactory %% [~Database, ~IdGenerator] ::
    ^[name: String, email: String] => *User :: {
        id = %idGenerator->generate;
        user = User[id: id, name: #name, email: #email];
        %database->save(user);
        user
    };
```

**Repository pattern:**

```walnut
UserRepository := $[
    findById: ^UserId => *Result<User, NotFound>,
    save: ^User => *UserId,
    delete: ^UserId => *Null
];

==> UserRepository %% ~Database :: UserRepository[
    findById: ^id: UserId => *Result<User, NotFound> :: %database->query(/* ... */),
    save: ^user: User => *UserId :: %database->insert(/* ... */),
    delete: ^id: UserId => *Null :: %database->delete(/* ... */)
];
```

**Service layer:**

```walnut
UserService := $[
    register: ^[name: String, email: String] => *Result<User, RegistrationError>,
    authenticate: ^[email: String, password: String] => *Result<Token, AuthError>
];

==> UserService %% [~UserRepository, ~PasswordHasher, ~TokenGenerator] ::
    UserService[
        register: ^[name: String, email: String] => *Result<User, RegistrationError> :: {
            /* Use injected dependencies */
            user = User[name: #name, email: #email];
            %userRepository->save(user)
        },
        authenticate: /* ... */
    ];
```

## Summary

Walnut's dependency injection system provides:

- **Compile-time resolution** - all dependencies verified before runtime
- **Type safety** - dependencies are strongly typed
- **Explicit declaration** - dependencies visible in signatures via `%%`
- **Automatic injection** - no manual wiring required
- **Flexible access** - via `%`, `%field`, named dependencies
- **Provider pattern** - define dependencies with `==> TypeName`
- **Dependency chains** - providers can depend on other types
- **Error detection** - circular dependencies and missing providers caught at compile time
- **Testability** - easy to swap implementations for testing
- **Clean architecture** - encourages separation of concerns

The DI system integrates seamlessly with functions, methods, constructors, and cast functions, making it a foundational feature for building modular, testable, and maintainable Walnut applications.
