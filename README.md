# Walnut Language

**A strongly-typed, functional programming language for modeling and executing business logic with precision and clarity**

[![Tests](https://github.com/kapitancho/lang-walnut/actions/workflows/tests.yml/badge.svg)](https://github.com/kapitancho/lang-walnut/actions/workflows/tests.yml)
[![codecov](https://codecov.io/gh/kapitancho/lang-walnut/graph/badge.svg?token=QDNK8CWZB3)](https://codecov.io/gh/kapitancho/lang-walnut)
[![Version](https://img.shields.io/github/v/release/kapitancho/lang-walnut?sort=semver)](https://github.com/kapitancho/lang-walnut/releases)
[![License](https://img.shields.io/github/license/kapitancho/lang-walnut)](https://github.com/kapitancho/lang-walnut/blob/main/LICENSE)
[![PHP](https://img.shields.io/packagist/php-v/walnut/lang)](https://packagist.org/packages/walnut/lang)

## Overview

Walnut is a modern functional programming language that combines powerful type system features with practical programming constructs. Designed for building robust applications, Walnut emphasizes type safety, immutability, and clear error handling while maintaining excellent developer ergonomics.

### Key Features

- **🎯 Expressive Type System** - Set-theory based types with union, intersection, and refinement types
- **🔒 Type Safety** - Strong static type checking with comprehensive type inference
- **⚡ Functional Core** - Immutability by default with first-class functions and lambda expressions
- **💉 Built-in Dependency Injection** - Compile-time dependency resolution and service management
- **🛡️ Sophisticated Error Handling** - Result types with automatic error propagation
- **🔧 Method-Oriented Design** - Behavior-driven functions attached to types
- **📦 Module System** - Package-based organization with explicit dependency management
- **🌐 JSON Integration** - First-class support for JSON parsing and type-safe hydration

## Quick Start

### Installation

Choose your installation method:

#### Option 1: Standalone CLI (Recommended for Development, requires PHP 8.4+ installed)

Download the latest **CLI bundle** (includes everything needed):

[Download walnut-cli.tar.gz](https://github.com/kapitancho/lang-walnut/releases/latest/download/walnut-cli.tar.gz)

Extract and run:
```bash
tar -xzf walnut-cli.tar.gz
cd walnut-cli-bundle

./walnut.phar hello-world
```

#### Option 2: Via Composer (For PHP Projects)

```bash
composer require walnut/lang
./vendor/bin/walnut hello-world
```

#### Option 3: Production HTTP Server with RoadRunner

Download the server bundle for your platform:

```bash
# Linux
tar -xzf walnut-roadrunner-linux-amd64.tar.gz
cd walnut-roadrunner-linux-amd64
./walnut-roadrunner --port 8084

# macOS (Intel)
tar -xzf walnut-roadrunner-darwin-amd64.tar.gz
cd walnut-roadrunner-darwin-amd64
./walnut-roadrunner --port 8084

# macOS (Apple Silicon)
tar -xzf walnut-roadrunner-darwin-arm64.tar.gz
cd walnut-roadrunner-darwin-arm64
./walnut-roadrunner --port 8084
```

### Hello World

Create a file `hello.nut`:

```walnut
module hello:

::> 'Hello, World!';
```

Run it:

```bash
# Using standalone phar
./walnut.phar hello

# Using composer installation
./vendor/bin/walnut hello

# Development server with live reload
./walnut.phar serve --port 3000
```

## Language Highlights

### Powerful Type System

Walnut features one of the most expressive type systems among programming languages:

```walnut
/* Refined types with constraints */
Age := Integer<0..150>;
Email := String<5..254> @ InvalidEmail :: /* Custom email validation */;
NonEmptyStringArray := Array<String, 1..>;

/* Union types in different forms */
MyResult := [status: String['success'], data: Any]
        | [status: String['error'], message: String];

/* Sealed types for data integrity */
User := $[id: Integer<1..>, name: String<1..>, email: Email];

/* Enumerations with pattern matching */
Status := (Pending, Active, Completed);
```

### Functional Programming

First-class functions, immutability, and powerful transformation operations:

```walnut
/* Lambda functions with named parameters */
double = ^x: Integer => Integer :: x * 2;

/* Array transformations */
numbers = [1, 2, 3, 4, 5];
doubled = numbers->map(double);
evens = numbers->filter(^i: Integer => Boolean :: i % 2 == 0);

/* Function composition */
processData = ^data: Array<Integer> ::
    data->filter(^x: Integer => Boolean :: x > 0)
        ->map(^x: Integer :: x * 2);

processed = processData([0, 1, -2, 3, -4, 5]);
/* Result: [2, 6, 10] */
```

### Error Handling with Result Types

Type-safe error handling without exceptions:

```walnut
Age = Integer<0..150>;

/* Result type for operations that may fail */
parseAge = ^input: String => Result<Age, NotANumber|String> :: {
    num = input => asInteger;  /* Propagates NotANumber error automatically */
    ?whenTypeOf(num) is {
        `Age: num,
        ~: => @'Age must be between 0 and 150'
    }
};

/* Pattern matching on results */
result = parseAge('25');
?whenIsError(result) { 'Could not parse age: ' + result->printed };
```

### Method-Oriented Design

Attach behavior to types with methods:

```walnut
/* Type definition */
Point := [x: Real, y: Real];

/* Methods on Point */
Point->distanceTo(^p: Point => Real<0..>) :: {
    dx = $x - p.x;
    dy = $y - p.y;
    dxSquared = dx * dx;
    dySquared = dy * dy;
    sum = dxSquared + dySquared;
    sum->sqrt
};

Point->translate(^delta: [dx: Real, dy: Real] => Point) :: {
    Point![x: $x + delta.dx, y: $y + delta.dy]
};

/* Usage */
p1 = Point![x: 0.0, y: 0.0];
p2 = Point![x: 3.0, y: 4.0];
distance = p1->distanceTo(p2);  /* 5.0 */
translated = p1->translate([dx: 1.0, dy: 2.0]);  /* [x: 1.0, y: 2.0] */
```

### Type-Safe JSON Hydration

Convert external data to typed values with validation:

```walnut
/* Define your data structure */
Email := #String<5..254> @ InvalidEmail :: /* Custom email validation */;
UserInput := $[
    username: String<3..20>,
    email: Email,
    age: Integer<13..>
];

/* Hydrate from JSON with automatic validation */
jsonData = '{"username":"alice","email":"alice@example.com","age":25}';
result = jsonData->jsonDecode => hydrateAs(`UserInput);

?whenIsError(result) {
    'Hydration failed: ' + result->printed
} ~ { 
    processUser(result)
};
```

### Built-in Dependency Injection
Every function or method can declare dependencies that are automatically resolved at compile time:

```walnut
/* The type to be injected */
ProjectById = ^ ~ProjectId => Result<Project, ProjectNotFound>;

/* Injected implementation */
==> ProjectById %% db: DatabaseConnection :: ... use db to fetch project ... ;

/* Using the injected function */
markProjectDone = ^id: ProjectId => Result<Null, ProjectNotFound> %% byId: ProjectById :: {
project = byId=>invoke(id); /* injected function called */
project->markDone;
...
};
```

## Documentation

Walnut comes with comprehensive documentation organized into 5 major parts:

### 📚 [Complete Language Specification](docs/00-index.md)

**Part I: Fundamentals**
- [Lexical Structure](docs/01-lexical-structure.md) - Tokens, keywords, operators
- [Type System](docs/02-type-system.md) - Type hierarchy and subtyping rules
- [Values and Literals](docs/03-values-literals.md) - Literal syntax and type inference

**Part II: Type Definitions**
- [User-Defined Types](docs/04-user-defined-types.md) - Atoms, enumerations, data types
- [Type Refinement](docs/05-type-refinement.md) - Constraints and value subsets
- [Integers and Reals](docs/06-integers-reals.md) - Numeric types and operations
- [Strings](docs/07-strings.md) - String operations and methods
- [Booleans](docs/08-booleans.md) - Boolean logic and operations
- [Arrays and Tuples](docs/09-arrays-tuples.md) - Collection types
- [Maps and Records](docs/10-maps-records.md) - Key-value structures
- [Union and Intersection Types](docs/11-union-intersection.md) - Complex type compositions
- [Mutable Values](docs/12-mutable-values.md) - Controlled mutability
- [Reflection and Metaprogramming](docs/13-reflection-metaprogramming.md) - Type introspection

**Part III: Functions, Methods, and Control Flow**
- [Functions](docs/14-functions.md) - Function syntax and parameters
- [Methods](docs/15-methods.md) - Method definitions and resolution
- [Constructors and Casts](docs/16-constructors-casts.md) - Type conversions
- [Expressions](docs/17-expressions.md) - Expression categories
- [Conditional Expressions](docs/18-conditional-expressions.md) - Pattern matching
- [Early Returns and Error Handling](docs/19-early-returns-errors.md) - Error propagation

**Part IV: Built-in Library**
- [Core Methods](docs/20-core-methods.md) - Built-in operations on all types
- [Standard Library Types](docs/21-standard-library.md) - Clock, Random, File, HTTP, Database

**Part V: Advanced Features**
- [Hydration](docs/22-hydration.md) - Runtime type conversion and validation
- [Module System](docs/23-module-system.md) - Code organization
- [Dependency Injection](docs/24-dependency-injection.md) - Service management
- [Entry Points](docs/25-entry-points.md) - CLI and HTTP interfaces
- [Testing](docs/26-testing.md) - Test framework
- [Templates](docs/27-templates.md) - HTML template system

## Examples

### Web Service Example

```walnut
module user-service %% $http/router, $db/connector:

User := $[id: Integer<1..>, username: String<3..20>, email: String];

createUser = ^[username: String, email: String] => *Result<User, String> :: {
    /* Validate input */
    ?when(#username->length < 3) {
        => @'Username too short'
    };

    /* Insert into database */
    sql = 'INSERT INTO users (username, email) VALUES (?, ?)';
    %databaseConnector |> execute(sql);

    /* Return created user */
    User[id: 1, username: #username, email: #email]
};

handleRequest = ^{HttpRequest} => {HttpResponse} :: {
    request = $->shape(`HttpRequest);

    ?whenValueOf(request.method) is {
        HttpRequestMethod.post: {
            userData = request.body->jsonDecode => hydrateAs(`[username: String, email: String]);
            result = createUser[username: userData.username, email: userData.email];

            ?whenTypeOf(result) is {
                `User: HttpResponse[status: 201, headers: [:], body: result->jsonStringify],
                `Error: HttpResponse[status: 400, headers: [:], body: [error: result->error]->jsonStringify]
            }
        },
        ~: HttpResponse[status: 405, headers: [:], body: 'Method not allowed']
    }
};
```

### Data Processing Pipeline

```walnut
module analytics:

Transaction := [id: Integer, amount: Real, category: String];

analyzeTransactions = ^transactions: Array<Transaction> :: {
    total = transactions->map(^t: Transaction => Real :: t.amount)->sum;

    /* Get unique categories */
    categories = transactions->map(^t: Transaction => String :: t.category)->unique;

    /* Calculate total per category */
    categoryTotals = categories->map(^cat: String :: [
        category: cat,
        total: transactions
            ->filter(^t: Transaction => Boolean :: t.category == cat)
            ->map(^t: Transaction => Real :: t.amount)
            ->sum
    ]);

    [total: total, byCategory: categoryTotals]
};

::> {
    transactions = [
        [id: 1, amount: 100.0, category: 'food'],
        [id: 2, amount: 50.0, category: 'transport'],
        [id: 3, amount: 75.0, category: 'food']
    ];

    result = analyzeTransactions(transactions);
    result->printed
    /* Output: [total: 225.0, byCategory: [...]] */
};
```

## Resources

- 🌐 **[Online Demos](https://demo.walnutphp.com/)** - Try Walnut in your browser
- 📦 **[Example Projects](https://github.com/kapitancho/lang-walnut-demos)** - Downloadable demo applications
- 📖 **[Full Documentation](docs/00-index.md)** - Complete language specification
- 🐛 **[Issue Tracker](https://github.com/kapitancho/walnut-lang/issues)** - Report bugs and request features

## Project Structure

```
walnut-lang/
├── cli/                      # CLI entry point
├── core-nut-lib/            # Core standard library (Walnut code)
├── docs/                    # Complete language documentation
│   ├── 00-index.md         # Documentation index
│   ├── 01-lexical-structure.md
│   ├── ...
│   └── 27-templates.md
├── src/                     # PHP implementation
├── walnut-src/             # Example Walnut programs
└── tests/                  # Test suite
```

## Philosophy

Walnut is designed around several core principles:

1. **Type Safety First** - Catch errors at compile time, not runtime
2. **Immutability by Default** - Predictable, side-effect-free code
3. **Explicit Over Implicit** - Clear intent and dependencies
4. **Business Logic Focus** - Express domain models precisely
5. **Developer Ergonomics** - Concise syntax without sacrificing clarity

## Distribution Models

Walnut is available in two independent distributions:

### walnut.phar (Pure CLI)
- **Size:** 3.0 MB
- **For:** Development, testing, scripts
- **Features:** CLI execution, testing framework, development HTTP server
- **Download:** From [GitHub Releases](https://github.com/walnut-lang/walnut/releases)

### walnut-roadrunner (Production Server)
- **Size:** 18-19 MB (platform-specific)
- **For:** Production HTTP servers
- **Features:** High-performance RoadRunner server, multiple workers
- **Platforms:** Linux x86-64, macOS Intel, macOS Apple Silicon
- **Download:** From [GitHub Releases](https://github.com/walnut-lang/walnut/releases)

**Why separate?** The CLI doesn't need RoadRunner code, so we keep it lightweight. The server bundle is self-contained and ready to deploy.

## Contributing

Contributions are welcome! Please feel free to submit pull requests, report bugs, or suggest features through the issue tracker.

## License

Walnut Language is open-source software licensed under the MIT license.

## Acknowledgments

Walnut draws inspiration from functional languages like Haskell and ML, type systems from TypeScript and Flow, and practical features from modern languages like Rust and Kotlin.

---

**Ready to start?** Check out the [Complete Documentation](docs/00-index.md) or try the [Online Demos](https://demo.walnutphp.com/)!
