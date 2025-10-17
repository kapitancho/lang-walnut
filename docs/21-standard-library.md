# 17. Standard Library Types

## Overview

Beyond the core language types, Walnut provides a standard library of specialized types for common tasks such as time operations, random number generation, file I/O, HTTP handling, database access, and more. These types are typically used through dependency injection.

## 17.1 Clock

The `Clock` type provides access to current time and date information.

### 17.1.1 Methods

**`now`** - Get current timestamp
```walnut
Clock->now(=> Integer)

/* Usage with dependency injection */
getCurrentTime = ^ => Integer %% ~Clock :: {
    clock->now
};
```

### 17.1.2 Usage Example

```walnut
module time-service:

TimeService = [
    getCurrentTimestamp: ^ => Integer,
    getFormattedTime: ^ => String
];

==> TimeService %% ~Clock :: {
    getCurrentTimestamp = ^ => Integer :: %clock->now;

    getFormattedTime = ^ => String :: {
        timestamp = %clock->now;
        /* Format timestamp */
        'Current time: ' + timestamp->asString
    };

    [~getCurrentTimestamp, ~getFormattedTime]
};
```

## 17.2 Random

The `Random` type provides random number generation and UUID generation.

### 17.2.1 Methods

**`integer`** - Generate random integer in range
```walnut
Random->integer([min: Integer, max: Integer] => Integer)

/* Generate random number between 1 and 100 */
randomNum = %random->integer[min: 1, max: 100];
```

**`uuid`** - Generate UUID
```walnut
Random->uuid(=> String)

/* Generate unique identifier */
id = %random->uuid;  /* e.g., '550e8400-e29b-41d4-a716-446655440000' */
```

### 17.2.2 Usage Example

```walnut
module dice-roller:

rollDice = ^ => Integer %% ~Random :: {
    random->integer[min: 1, max: 6]
};

generateId = ^ => String %% ~Random :: {
    random->uuid
};

==> DiceService :: [~rollDice, ~generateId];
```

## 17.3 File

The `File` type provides file system operations. All file operations are impure (may throw `ExternalError`).

### 17.3.1 Methods

**`content`** - Read file contents
```walnut
File->content(Null => *String)

readFile = ^path: String => *String %% [~File] :: {
    file = %file->locate(path);
    file |> content
};
```

**`createIfMissing`** - Create file if it doesn't exist
```walnut
File->createIfMissing(String => *Null)

ensureFile = ^path: String => *Null %% [~File] :: {
    file = %file->locate(path);
    file |> createIfMissing('')
};
```

**`appendContent`** - Append to file
```walnut
File->appendContent(String => *Null)

appendToLog = ^message: String => *Null %% [~File] :: {
    logFile = %file->locate('app.log');
    logFile |> appendContent(message + '\n')
};
```

**`replaceContent`** - Replace file contents
```walnut
File->replaceContent(String => *Null)

writeFile = ^[path: String, content: String] => *Null %% [~File] :: {
    file = %file->locate(path);
    file |> replaceContent(content)
};
```

### 17.3.2 Usage Example

```walnut
module file-service %% [~File]:

readConfig = ^Null => *[host: String, port: Integer] :: {
    configFile = %file->locate('config.json');
    content = configFile |> content;
    json = content => jsonDecode;
    json => hydrateAs(type[host: String, port: Integer])
};

writeLog = ^message: String => *Null :: {
    logFile = %file->locate('app.log');
    timestamp = /* get timestamp */;
    logFile |> appendContent(timestamp + ': ' + message + '\n')
};

==> LogService :: [~writeLog];
```

## 17.4 JsonValue

The `JsonValue` type represents JSON data and provides hydration capabilities.

### 17.4.1 Methods

**`hydrateAs`** - Hydrate JSON to typed value
```walnut
JsonValue->hydrateAs(Type<T> => Result<T, HydrationError>)

UserType = [id: Integer, name: String, email: String];
jsonString = '{"id":1,"name":"Alice","email":"alice@example.com"}';
json = jsonString => jsonDecode;
user = json => hydrateAs(`UserType);
/* user = [id: 1, name: 'Alice', email: 'alice@example.com'] */
```

**`stringify`** - Convert to JSON string
```walnut
JsonValue->stringify(Null => String)

json->stringify;
```

### 17.4.2 Hydration Examples

```walnut
/* Simple hydration */
json = '{"a":1,"b":"hello"}' => jsonDecode;
record = json => hydrateAs(type[a: Integer, b: String]);

/* Nested hydration */
UserProfile = [
    id: Integer,
    name: String,
    address: [city: String, country: String]
];

jsonStr = '{
    "id": 1,
    "name": "Alice",
    "address": {"city": "NYC", "country": "USA"}
}';

profile = jsonStr => jsonDecode => hydrateAs(`UserProfile);

/* Array hydration */
jsonArray = '[1, 2, 3, 4, 5]' => jsonDecode;
numbers = jsonArray => hydrateAs(`Array<Integer>);
```

## 17.5 HttpRequest and HttpResponse

Types for HTTP server applications.

### 17.5.1 HttpRequest

Represents an HTTP request with methods, headers, body, etc.

**Type structure (sealed):**
```walnut
HttpRequest := $[
    method: HttpMethod,
    path: String,
    headers: Map<String>,
    body: String,
    queryParams: Map<String>
];

HttpMethod = String['GET', 'POST', 'PUT', 'DELETE', 'PATCH'];
```

### 17.5.2 HttpResponse

Represents an HTTP response.

**Type structure (sealed):**
```walnut
HttpResponse := $[
    status: Integer<100..599>,
    headers: Map<String>,
    body: String
];
```

### 17.5.3 HTTP Server Example

```walnut
module api-server:

handleRequest = ^{HttpRequest} => {HttpResponse} :: {
    request = $->shape(`HttpRequest);

    response = ?whenValueOf(request.method) is {
        'GET': handleGet(request),
        'POST': handlePost(request),
        ~: HttpResponse[
            status: 405,
            headers: [:],
            body: 'Method not allowed'
        ]
    };

    response->shape(`{HttpResponse})
};

handleGet = ^HttpRequest => HttpResponse :: {
    HttpResponse[
        status: 200,
        headers: [contentType: 'application/json'],
        body: '{"message":"Hello, World!"}'
    ]
};

handlePost = ^HttpRequest => HttpResponse :: {
    /* Process POST request */
    HttpResponse[
        status: 201,
        headers: [:],
        body: '{"status":"created"}'
    ]
};

==> HttpServer :: handleRequest;
```

## 17.6 DatabaseConnector

Provides database access capabilities. All operations are impure.

### 17.6.1 Methods

**`execute`** - Execute SQL statement (no results)
```walnut
DatabaseConnector->execute(String => *Null)

createTable = ^Null => *Null %% [~DatabaseConnector] :: {
    sql = 'CREATE TABLE users (id INTEGER PRIMARY KEY, name TEXT)';
    %databaseConnector |> execute(sql)
};
```

**`query`** - Execute SQL query (with results)
```walnut
DatabaseConnector->query(String => *Array<Map>)

getUsers = ^Null => *Array<[id: Integer, name: String]> %% [~DatabaseConnector] :: {
    rows = %databaseConnector |> query('SELECT id, name FROM users');
    rows->map(^row => [id: Integer, name: String] :: {
        [
            id: row->item('id') => asInteger,
            name: row->item('name') => asString
        ]
    })
};
```

### 17.6.2 Usage Example

```walnut
module user-repository %% [~DatabaseConnector]:

UserRecord = [id: Integer, name: String, email: String];

createUser = ^[name: String, email: String] => *Integer :: {
    sql = 'INSERT INTO users (name, email) VALUES (?, ?)';
    %databaseConnector |> execute(sql); /* Simplified */
    /* Return inserted ID */
    1
};

findUserById = ^id: Integer => *Result<UserRecord, String> :: {
    sql = 'SELECT id, name, email FROM users WHERE id = ?';
    rows = %databaseConnector |> query(sql);

    ?when(rows->length == 0) {
        => @'User not found'
    };

    row = rows->item(0);
    UserRecord[
        id: row->item('id') => asInteger,
        name: row->item('name') => asString,
        email: row->item('email') => asString
    ]
};

getAllUsers = ^Null => *Array<UserRecord> :: {
    rows = %databaseConnector |> query('SELECT id, name, email FROM users');
    rows->map(^row => UserRecord ::
        UserRecord[
            id: row->item('id') => asInteger,
            name: row->item('name') => asString,
            email: row->item('email') => asString
        ]
    )
};

==> UserRepository :: [~createUser, ~findUserById, ~getAllUsers];
```

## 17.7 EventBus

Provides event-driven programming support.

### 17.7.1 Type Definition

```walnut
EventBus := $[listeners: Array<EventListener>];
EventListener = ^Any => *Null;
```

### 17.7.2 Methods

**`fire`** - Fire event to all listeners
```walnut
EventBus->fire(Any => *Any)

notifyListeners = ^event: Any => *Null %% [~EventBus] :: {
    %eventBus |> fire(event)
};
```

### 17.7.3 Usage Example

```walnut
module event-service:

UserCreatedEvent = [userId: Integer, name: String];
OrderPlacedEvent = [orderId: Integer, total: Real];

/* Define listeners */
userCreatedListener = ^UserCreatedEvent => *Null :: {
    /* Handle user created event */
    'User created: ' + #name->DUMPNL;
    null
};

orderPlacedListener = ^OrderPlacedEvent => *Null :: {
    /* Handle order placed event */
    'Order placed: ' + #orderId->asString->DUMPNL;
    null
};

/* Create event bus */
==> EventBus :: EventBus[listeners: [
    userCreatedListener,
    orderPlacedListener
]];

/* Fire events */
fireUserCreated = ^userId: Integer, name: String => *Null %% [~EventBus] :: {
    event = UserCreatedEvent![~userId, ~name];
    %eventBus |> fire(event)
};
```

## 17.8 PasswordString

Provides secure password hashing and verification.

### 17.8.1 Type Definition

```walnut
PasswordString := $String;
```

### 17.8.2 Methods

**`hash`** - Hash password
```walnut
String->hash(Null => PasswordString)

hashPassword = ^plaintext: String => PasswordString :: {
    plaintext->hash
};
```

**`verify`** - Verify password against hash
```walnut
PasswordString->verify(String => Boolean)

checkPassword = ^[hash: PasswordString, plaintext: String] => Boolean :: {
    hash->verify(plaintext)
};
```

### 17.8.3 Usage Example

```walnut
module auth-service:

User = [id: Integer, username: String, passwordHash: PasswordString];

registerUser = ^[username: String, password: String] => User :: {
    hash = password->hash;
    User[
        id: 1, /* Generated */
        username: username,
        passwordHash: hash
    ]
};

authenticateUser = ^[user: User, password: String] => Boolean :: {
    user.passwordHash->verify(password)
};

==> AuthService :: [~registerUser, ~authenticateUser];
```

## 17.9 RoutePattern

Provides URL pattern matching for HTTP routing.

### 17.9.1 Type Definition

```walnut
RoutePattern := $String;
```

### 17.9.2 Methods

**`matchAgainst`** - Match URL against pattern
```walnut
RoutePattern->matchAgainst(String => Result<Map<String>, RouteNotMatched>)

matchRoute = ^[pattern: RoutePattern, path: String] => Result<Map<String>, RouteNotMatched> :: {
    pattern->matchAgainst(path)
};
```

### 17.9.3 Usage Example

```walnut
module router:

Route = [pattern: RoutePattern, handler: ^Map<String> => HttpResponse];

routes = [
    Route[
        pattern: RoutePattern('/users/:id'),
        handler: ^params: Map<String> => HttpResponse :: {
            userId = params->item('id');
            /* Handle request */
            HttpResponse[status: 200, headers: [:], body: 'User: ' + userId]
        }
    ],
    Route[
        pattern: RoutePattern('/products/:category/:id'),
        handler: ^params: Map<String> => HttpResponse :: {
            category = params->item('category');
            productId = params->item('id');
            /* Handle request */
            HttpResponse[status: 200, headers: [:], body: 'Product']
        }
    ]
];

matchRoute = ^path: String => Result<HttpResponse, String> :: {
    /* Try each route */
    routes->findFirst(^route => Boolean :: {
        match = route.pattern->matchAgainst(path);
        !match->isOfType(`Error)
    }) => map(^route => HttpResponse :: {
        params = route.pattern->matchAgainst(path);
        route.handler(params)
    })
};
```

## 17.10 DependencyContainer

Provides access to the dependency injection container.

### 17.10.1 Methods

**`valueOf`** - Get dependency by type
```walnut
DependencyContainer->valueOf(Type<T> => T)

getDependency = ^depType: Type<T> => T %% [~DependencyContainer] :: {
    %dependencyContainer->valueOf(depType)
};
```

### 17.10.2 Usage Example

```walnut
module service-locator %% [~DependencyContainer]:

/* Dynamic dependency resolution */
getService = ^serviceType: Type<T> => T :: {
    %dependencyContainer->valueOf(serviceType)
};

/* Get database at runtime */
getDatabaseService = ^Null => DatabaseConnector :: {
    %dependencyContainer->valueOf(`DatabaseConnector)
};

==> ServiceLocator :: [~getService, ~getDatabaseService];
```

## 17.11 Practical Patterns

### 17.11.1 Configuration Service

```walnut
module config-service %% [~File]:

Config = [
    database: [host: String, port: Integer],
    server: [port: Integer],
    logging: [level: String]
];

loadConfig = ^Null => *Config :: {
    configFile = %file->locate('config.json');
    content = configFile |> content;
    json = content => jsonDecode;
    json => hydrateAs(`Config)
};

==> Config :: loadConfig();
```

### 17.11.2 Logging Service

```walnut
module logger %% [~File, ~Clock]:

LogLevel = String['debug', 'info', 'warning', 'error'];

log = ^[level: LogLevel, message: String] => *Null :: {
    timestamp = %clock->now;
    logFile = %file->locate('app.log');
    entry = timestamp->asString + ' [' + level + '] ' + message + '\n';
    logFile |> appendContent(entry)
};

info = ^message: String => *Null :: log[level: 'info', message: message];
error = ^message: String => *Null :: log[level: 'error', message: message];

==> Logger :: [~info, ~error];
```

### 17.11.3 User Service with Database

```walnut
module user-service %% [~DatabaseConnector, ~PasswordString]:

User = [id: Integer, username: String, email: String];
UserWithPassword = [id: Integer, username: String, email: String, passwordHash: PasswordString];

createUser = ^[username: String, email: String, password: String] => *Result<User, String> :: {
    /* Hash password */
    passwordHash = password->hash;

    /* Insert into database */
    sql = 'INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)';
    %databaseConnector |> execute(sql);

    /* Return user */
    User[id: 1, username: username, email: email]
};

authenticateUser = ^[username: String, password: String] => *Result<User, String> :: {
    /* Query database */
    sql = 'SELECT id, username, email, password_hash FROM users WHERE username = ?';
    rows = %databaseConnector |> query(sql);

    ?when(rows->length == 0) {
        => @'User not found'
    };

    row = rows->item(0);
    storedHash = PasswordString(row->item('password_hash')->asString);

    /* Verify password */
    ?when(!storedHash->verify(password)) {
        => @'Invalid password'
    };

    /* Return user */
    User[
        id: row->item('id') => asInteger,
        username: row->item('username') => asString,
        email: row->item('email') => asString
    ]
};

==> UserService :: [~createUser, ~authenticateUser];
```

### 17.11.4 HTTP API with Routing

```walnut
module api %% [~UserService]:

handleRequest = ^{HttpRequest} => {HttpResponse} :: {
    request = $->shape(`HttpRequest);

    response = ?whenValueOf(request.path) is {
        '/users': handleUsers(request),
        ~: {
            /* Try pattern matching */
            userPattern = RoutePattern('/users/:id');
            match = userPattern->matchAgainst(request.path);
            ?whenIsError(match) {
                HttpResponse[status: 404, headers: [:], body: 'Not found']
            } ~ {
                handleUser(request, match)
            }
        }
    };

    response->shape(`{HttpResponse})
};

handleUsers = ^HttpRequest => HttpResponse :: {
    ?whenValueOf($.method->shape(`String)) is {
        'GET': {
            /* List users */
            HttpResponse[status: 200, headers: [:], body: '[]']
        },
        'POST': {
            /* Create user */
            HttpResponse[status: 201, headers: [:], body: '{}']
        },
        ~: HttpResponse[status: 405, headers: [:], body: 'Method not allowed']
    }
};

handleUser = ^[HttpRequest, Map<String>] => HttpResponse :: {
    userId = #1->item('id');
    HttpResponse[status: 200, headers: [:], body: 'User: ' + userId]
};

==> HttpServer :: handleRequest;
```

## 17.12 Best Practices

### 17.12.1 Use Dependency Injection

```walnut
/* Good: Declare dependencies explicitly */
module service %% [~Database, ~Logger]:

/* Avoid: Direct instantiation */
/* database = Database('connection-string'); */
```

### 17.12.2 Handle Impure Operations

```walnut
/* Good: Mark impure operations */
readConfig = ^Null => *Config %% [~File] :: {
    /* ... */
};

/* Good: Handle external errors */
result = file |> content;
```

### 17.12.3 Type Safety with Hydration

```walnut
/* Good: Use typed hydration */
UserType = [id: Integer, name: String];
user = json => hydrateAs(`UserType);

/* Avoid: Using untyped JSON */
/* user = json; */
```

### 17.12.4 Separate Concerns

```walnut
/* Good: Separate repository, service, and controller layers */
module user-repository %% [~DatabaseConnector]:
    /* Data access */

module user-service %% [~UserRepository]:
    /* Business logic */

module user-controller %% [~UserService]:
    /* HTTP handling */
```

## Summary

Walnut's standard library provides:

- **Clock** - Time and date operations
- **Random** - Random number and UUID generation
- **File** - File system operations
- **JsonValue** - JSON parsing and hydration
- **HttpRequest/HttpResponse** - HTTP server support
- **DatabaseConnector** - Database access
- **EventBus** - Event-driven programming
- **PasswordString** - Secure password handling
- **RoutePattern** - URL pattern matching
- **DependencyContainer** - Dynamic dependency resolution

All standard library types:
- Work through dependency injection
- Support impure operations with proper error handling
- Provide type-safe interfaces
- Enable building robust applications
- Follow consistent patterns

These types enable building complete applications including web servers, APIs, database-backed systems, and more, while maintaining Walnut's type safety and functional programming principles.
