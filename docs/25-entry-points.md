# Entry Points

## Overview

Entry points define how Walnut programs interact with the external world. They specify the interface through which a module can be executed, whether from the command line, as an HTTP server, or through direct function invocation.

A Walnut module can have multiple entry points of different types, allowing a single module to serve multiple purposes (e.g., both CLI tool and HTTP API).

## CLI Entry Point

### Basic Syntax

The CLI entry point uses the `::>` operator, which is shorthand for defining a function that matches the `CliEntryPoint` type:

```walnut
::> expression
```

This is equivalent to:

```walnut
==> CliEntryPoint :: ^args: Array<String> => String :: expression
```

**Key Points:**
- The `args` variable contains command-line arguments as an array of strings
- The expression must evaluate to a `String`, which becomes the program's output
- Arguments are passed from the command line after the module name

### Simple Examples

#### Hello World

The simplest CLI entry point:

```walnut
module hello:

::> 'Hello, World!'
```

When executed, this outputs: `Hello, World!`

#### Accessing Arguments

Use the `args` variable to access command-line arguments:

```walnut
module greet:

::> {
    name = ?noError(args->item(0));
    ?whenIsError(name) {
        'Please provide a name'
    } ~ {
        'Hello, ' + name + '!'
    }
}
```

Usage:
```bash
$ walnut greet Alice
Hello, Alice!
```

### Full Form with Type Annotation

The complete syntax for a CLI entry point includes the full type signature:

```walnut
==> CliEntryPoint :: ^args: Array<String> => String :: expression
```

**Example:**

```walnut
module calc:

==> CliEntryPoint :: ^args: Array<String> => String :: {
    a = ?noError(?noError(args->item(0))->asInteger);
    b = ?noError(?noError(args->item(1))->asInteger);

    ?whenIsError(a) {
        'Error: First argument must be an integer'
    } ~ ?whenIsError(b) {
        'Error: Second argument must be an integer'
    } ~ {
        result = a + b;
        result->asString
    }
}
```

### With Dependencies

CLI entry points can declare dependencies using the `%%` syntax:

```walnut
%% DepType ::> expression
```

Full form:

```walnut
==> CliEntryPoint :: ^args: Array<String> => String %% DepType :: expression
```

**Example with Clock dependency:**

```walnut
module timestamp:

%% ~Clock ::> {
    currentTime = %clock->now;
    'Current timestamp: ' + currentTime->asString
}
```

**Example with multiple dependencies:**

```walnut
module user-manager %% [~Database, ~Logger]:

::> {
    %logger.log('Starting user manager');

    userId = ?noError(?noError(args->item(0))->asInteger);
    ?whenIsError(userId) {
        'Error: Provide a valid user ID'
    } ~ {
        user = %database.findUser(userId);
        ?whenIsError(user) {
            'User not found'
        } ~ {
            user->printed
        }
    }
}
```

### Complex Example from demo-cli.nut

Real-world calculator example:

```walnut
module demo-cli:

::> {
    calc = ^Array<String> => Result<Integer, Any> ::
        ?noError(?noError(#->item(0))->asInteger) +
        ?noError(?noError(#->item(1))->asInteger);

    s = calc(args);
    ?whenTypeOf(s) is {
        `Integer: s->asString,
        ~: 'Invalid parameters'
    }
}
```

This example:
1. Defines a local function `calc` that attempts to parse two integers and add them
2. Calls the function with the `args` array
3. Uses pattern matching to return either the result or an error message

## CliEntryPoint Type

### Type Definition

The `CliEntryPoint` type is defined in the core library:

```walnut
CliEntryPoint = ^Array<String> => String;
```

This is a **type alias** for a function that:
- Takes an `Array<String>` (command-line arguments)
- Returns a `String` (the program output)

### Resolution and Execution

When a Walnut module is executed from the command line:

1. The runtime looks for a function implementation with the `CliEntryPoint` type
2. Command-line arguments are collected into an array of strings
3. The function is invoked with the arguments
4. The returned string is written to stdout
5. If an error occurs, it's written to stderr and the process exits with a non-zero code

### Multiple CLI Entry Points

A module can define multiple CLI entry points by creating different functions:

```walnut
module multi-cli:

MainCli = ^Array<String> => String;
==> MainCli :: ^args: Array<String> => String :: 'Main: ' + args->printed;

AlternativeCli = ^Array<String> => String;
==> AlternativeCli :: ^args: Array<String> => String :: 'Alt: ' + args->printed;

/* Default CLI entry point */
::> args->printed
```

However, the `::>` operator defines the **default** CLI entry point.

## HTTP Entry Point

### Entry Point Type

HTTP entry points use the `HttpRequestHandler` type:

```walnut
HttpRequestHandler = ^{HttpRequest} => {HttpResponse};
```

This is a function that:
- Takes a shape matching `HttpRequest` as input
- Returns a shape matching `HttpResponse` as output

### HttpRequest Type

Defined in `$http/message`:

```walnut
HttpRequest = [
    protocolVersion: HttpProtocolVersion,
    method: HttpRequestMethod,
    target: HttpRequestTarget,
    headers: HttpHeaders,
    body: HttpMessageBody
]
```

**Where:**
- `HttpProtocolVersion` is an enumeration: `(http_1_0, http_1_1, http_2, http_3)`
- `HttpRequestMethod` is an enumeration: `(connect, delete, get, head, options, patch, post, put, trace)`
- `HttpRequestTarget` is a `String` (the URI path)
- `HttpHeaders` is `Map<Array<String, 1..>>` (header name to array of values)
- `HttpMessageBody` is `String|Null` (request body content)

### HttpResponse Type

Also defined in `$http/message`:

```walnut
HttpResponse = [
    protocolVersion: HttpProtocolVersion,
    statusCode: HttpResponseStatusCode,
    headers: HttpHeaders,
    body: HttpMessageBody
]
```

**Where:**
- `HttpResponseStatusCode` is an integer range of valid HTTP status codes
- Other fields match the request types

### Basic HTTP Handler Example

```walnut
module simple-api %% $http/message:

IndexHandler := ();
IndexHandler ==> HttpRequestHandler %% ~HttpResponseBuilder ::
    ^request: {HttpRequest} => {HttpResponse} :: {
        httpResponseBuilder(200)->withBody('<h1>Hello world</h1>')
    }
```

### Building Responses

The `HttpResponseBuilder` is a helper for constructing responses:

```walnut
HttpResponseBuilder = ^HttpResponseStatusCode => HttpResponse;
```

**Usage:**

```walnut
/* Create a 200 OK response */
response = httpResponseBuilder(200)
    ->withHeader[headerName: 'Content-Type', values: ['text/html']]
    ->withBody('<h1>Welcome</h1>');

/* Create a 404 Not Found response */
notFound = httpResponseBuilder(404)
    ->withBody('Page not found');

/* Create a 500 Internal Server Error */
error = httpResponseBuilder(500)
    ->withBody('Something went wrong');
```

### HTTP Entry Point with Dependencies

```walnut
module api %% $http/message:

UserHandler := ();
UserHandler ==> HttpRequestHandler %% [~HttpResponseBuilder, ~Database] ::
    ^request: {HttpRequest} => {HttpResponse} :: {
        req = request->shape(`HttpRequest);

        userId = req.target->substringRange[start: 7, end: 100];
        user = %database.findUser(userId);

        ?whenIsError(user) {
            %httpResponseBuilder(404)->withBody('User not found')
        } ~ {
            %httpResponseBuilder(200)->withBody(user->printed)
        }
    }
```

### Complete HTTP Example from all.test.nut

```walnut
module $http/all-test %% $http/bundle/autowire-router,
    $http/autowire/request-helper, $http/autowire/response-helper:

IndexHandler := ();
IndexHandler ==> HttpRequestHandler %% ~HttpResponseBuilder ::
    ^request: {HttpRequest} => {HttpResponse} :: {
        httpResponseBuilder(200)
            ->withBody('<h1>Hello world</h1>' + request->shape(`HttpRequest).target)
    };

AboutHandler := ();
AboutHandler ==> HttpRequestHandler %% ~HttpResponseBuilder ::
    ^request: {HttpRequest} => {HttpResponse} :: {
        httpResponseBuilder(200)
            ->withBody('<h1>About</h1>' + request->shape(`HttpRequest).body->asString)
    };

==> HttpLookupRouterMapping :: [
    [path: '/about', type: `AboutHandler, method: HttpRequestMethod.post],
    [path: '/index', type: `IndexHandler],
    [path: '', type: `HttpAutoWireRequestHandler]
];
```

## Function Invocation Interface

### Direct External Calls

Any function type accessible via the DependencyContainer can be used as an entry point.
This allows Walnut modules to expose functionality that can be invoked directly from the host language runtime (PHP).

**Example module:**

```walnut
module calculator:

CustomEntryPoint = ^Integer => String;

==> CustomEntryPoint :: ^n: Integer => String :: (n * 3)->asString;
```

**Usage from host language (PHP):**

```php
$result = $program->getEntryPoint(new TypeNameIdentifier('CustomEntryPoint'))->call(10);
// Returns: '30'
```

### Use Cases

1. **Library Modules**: Modules that export pure functions for use by other modules
2. **Testing**: Direct invocation of specific functions for unit testing
3. **Embedded Usage**: Using Walnut as a scripting engine within larger applications
4. **Service Integration**: Calling Walnut functions from other language runtimes

### Example: Math Library

```walnut
module math-lib:

/* Type definitions */
SquareRoot = ^Real<0..> => Real;
Power = ^[base: Real, exponent: Real] => Real;
Factorial = ^Integer<0..> => Integer;

/* Implementations */
==> SquareRoot :: ^n: Real<0..> => Real :: n->squareRoot;

==> Power :: ^[base: Real, exponent: Real] => Real ::
    base->power(exponent);

==> Factorial :: ^n: Integer<0..> => Integer ::
    ?when(n <= 1) { 1 } ~ { n * Factorial(n - 1) }
```

These functions can be invoked individually by the runtime.

## Multiple Entry Points

### Coexistence

A module can have multiple entry points of different types:

```walnut
module user-service %% [~Database, ~Logger]:

/* CLI Entry Point */
::> {
    command = ?noError(args->item(0));
    ?whenValueOf(command) is {
        'list': listUsers(),
        'add': addUser(args),
        ~: 'Unknown command'
    }
};

/* HTTP Entry Point */
UserApiHandler := ();
UserApiHandler ==> HttpRequestHandler %% [~HttpResponseBuilder, ~Database] ::
    ^request: {HttpRequest} => {HttpResponse} :: {
        /* Handle HTTP requests */
        %httpResponseBuilder(200)->withBody('API response')
    };

/* Function Invocation Interface */
GetUser = ^userId: Integer => Result<User, String>;
==> GetUser :: ^userId: Integer => Result<User, String> %% ~Database :: {
    %database.findUser(userId)
};
```

### Differentiation

Entry points are differentiated by:

1. **Type signature**: CLI uses `^Array<String> => String`, HTTP uses `^{HttpRequest} => {HttpResponse}`
2. **Declaration method**:
   - `::>` for default CLI entry point
   - `==>` with type name for named entry points
3. **Runtime selection**: The runtime decides which entry point to use based on execution context

### Example: Multi-Purpose Module

```walnut
module todo-app %% [~Database, ~Clock]:

/* CLI: Manage todos from command line */
::> {
    action = ?noError(args->item(0));
    ?whenValueOf(action) is {
        'add': addTodo(args->slice[1, 999]),
        'list': listTodos(),
        'done': markDone(args),
        ~: 'Usage: todo-app [add|list|done] [args...]'
    }
};

/* HTTP: REST API for todos */
TodoApiHandler := ();
TodoApiHandler ==> HttpRequestHandler %% [~HttpResponseBuilder, ~Database] ::
    ^request: {HttpRequest} => {HttpResponse} :: {
        req = request->shape(`HttpRequest);
        ?whenValueOf(req.method) is {
            HttpRequestMethod.get: listTodosHttp(),
            HttpRequestMethod.post: createTodoHttp(req.body),
            ~: %httpResponseBuilder(405)->withBody('Method not allowed')
        }
    };

/* Functions: Library API */
GetTodo = ^id: Integer => Result<Todo, String>;
==> GetTodo :: ^id: Integer => Result<Todo, String> %% ~Database ::
    %database.findTodo(id);

/* Helper functions (not entry points) */
addTodo = ^args: Array<String> => String %% [~Database, ~Clock] :: {
    description = args->combineAsString(' ');
    todo = %database.createTodo[
        description: description,
        createdAt: %clock->now
    ];
    'Added todo: ' + todo.id->asString
};

listTodos = ^=> String %% ~Database :: {
    todos = %database.listAllTodos();
    todos->map(^t :: t.description)->combineAsString('\n')
};
```

## Execution Flow

### CLI Execution Flow

1. **Module Loading**: Runtime loads the specified module and its dependencies
2. **Dependency Resolution**: The DependencyContainer resolves all dependencies
3. **Entry Point Lookup**: Runtime finds the CLI entry point (function matching `CliEntryPoint`)
4. **Argument Collection**: Command-line arguments are collected into `Array<String>`
5. **Dependency Injection**: Dependencies are injected into the function
6. **Execution**: The entry point function is called with arguments
7. **Output**: The returned string is written to stdout
8. **Error Handling**: Any errors are caught and written to stderr

**Example execution:**

```bash
$ walnut demo-cli 10 20
```

Flow:
1. Load `demo-cli` module
2. Resolve dependencies (none in this case)
3. Find `::> { ... }` entry point
4. Create `args = ['10', '20']`
5. Execute the function body
6. Return `'30'`
7. Write `30` to stdout

### HTTP Execution Flow

1. **Server Initialization**: HTTP server is started with the specified module
2. **Module Loading**: Module and dependencies are loaded once at startup
3. **Request Reception**: HTTP server receives an incoming request
4. **Request Mapping**: Request is converted to `HttpRequest` record
5. **Entry Point Invocation**: The `HttpRequestHandler` function is called
6. **Dependency Injection**: Dependencies are provided to the handler
7. **Response Generation**: Handler returns `HttpResponse` record
8. **Response Mapping**: Response is converted to HTTP protocol format
9. **Response Transmission**: HTTP response is sent to client

**Example:**

```bash
$ walnut-serve user-api
```

Flow per request:
1. Receive HTTP GET request to `/users/123`
2. Create `HttpRequest` with method=get, target='/users/123', etc.
3. Call handler function with the request
4. Handler processes request, queries database
5. Handler returns `HttpResponse` with status=200, body='{"id":123,...}'
6. Convert to HTTP response
7. Send to client

### Dependency Injection at Entry

Dependencies declared with `%%` are resolved at entry point invocation:

**Example:**

```walnut
module app %% [~Database, ~Clock, ~Logger]:

::> {
    /* All three dependencies are available here */
    %logger.log('Application started at ' + {%clock->now->asString});

    users = %database.getAllUsers();
    'Found ' + {users->length->asString} + ' users'
}
```

**Resolution process:**

1. Runtime identifies needed dependencies: `Database`, `Clock`, `Logger`
2. DependencyContainer resolves each type:
   - Looks for implementations in module and imports
   - Creates instances with their own dependencies
   - Handles circular dependencies (error if detected)
3. Dependencies are provided as the `%` variable
4. Entry point executes with full dependency context

### Error Handling at Entry

Errors at the entry point level are handled gracefully:

#### CLI Error Handling

```walnut
module safe-cli:

::> {
    value = ?noError(?noError(args->item(0))->asInteger);
    ?whenIsError(value) {
        'Error: Please provide a valid integer'
    } ~ {
        result = 100 / value;
        ?whenIsError(result) {
            'Error: Division by zero'
        } ~ {
            result->asString
        }
    }
}
```

If an uncaught error escapes:
- The error is serialized to a string
- Written to stderr
- Process exits with non-zero code

#### HTTP Error Handling

```walnut
module safe-api %% $http/message:

ApiHandler := ();
ApiHandler ==> HttpRequestHandler %% [~HttpResponseBuilder, ~Database] ::
    ^request: {HttpRequest} => {HttpResponse} :: {
        userId = ?noError(request.target->asInteger);
        ?whenIsError(userId) {
            %httpResponseBuilder(400)->withBody('Invalid user ID')
        } ~ {
            user = %database.findUser(userId);
            ?whenIsError(user) {
                %httpResponseBuilder(404)->withBody('User not found')
            } ~ {
                %httpResponseBuilder(200)->withBody(user->printed)
            }
        }
    }
```

If an uncaught error occurs:
- Automatically converted to HTTP 500 Internal Server Error
- Error details included in response body (in development mode)
- Logged to error log

#### Dependency Resolution Errors

```walnut
module broken-app:

/* Dependency that doesn't exist */
::> %nonExistentService.doSomething()
```

Error at startup:
- `DependencyContainerError` is raised
- Error message indicates which dependency couldn't be resolved
- Application fails to start with descriptive error

**Error types:**

```walnut
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

## Summary

Entry points provide three main ways to execute Walnut code:

1. **CLI Entry Points** (`::>`):
   - Simple command-line tools
   - Takes string array arguments
   - Returns string output
   - Type: `^Array<String> => String`

2. **HTTP Entry Points** (`HttpRequestHandler`):
   - Web servers and REST APIs
   - Takes HTTP request records
   - Returns HTTP response records
   - Type: `^{HttpRequest} => {HttpResponse}`

3. **Function Invocation**:
   - Direct function calls from host runtime
   - Any global function can be invoked
   - Useful for libraries and embedded usage

All entry points support:
- Dependency injection via `%%` syntax
- Type-safe interfaces
- Error handling through Result types
- Multiple entry points per module

The choice of entry point depends on the use case: CLI for command-line tools, HTTP for web services, and function invocation for library modules or embedded usage.
