# 11. Early Returns and Error Handling

## Overview

Walnut provides a sophisticated error handling system based on the `Result` type and early return mechanisms. This approach combines the safety of explicit error handling with the convenience of exception-like control flow, without the unpredictability of traditional exceptions.

## 11.1 The Result Type

### 11.1.1 Basic Result Type

The `Result<OkType, ErrorType>` type represents a computation that can either succeed with a value of `OkType` or fail with an error of `ErrorType`.

**Type definition:**
```walnut
Result<OkType, ErrorType>
```

**Examples:**
```walnut
/* Function that may fail */
divide = ^[numerator: Real, denominator: Real] => Result<Real, String> ::
    ?when(denominator == 0) {
        @'Division by zero'
    } ~ {
        numerator / denominator
    };

/* Result values */
success = 42;
failure = @'Something went wrong';
```

### 11.1.2 Error Type

The `Error<T>` type is a shorthand for `Result<Nothing, T>`, representing only error values.

**Type definition:**
```walnut
Error<T> = Result<Nothing, T>
```

**Examples:**
```walnut
/* Error-only type */
ValidationError = Error<String>;

/* Creating error values */
notFound = @'File not found';
invalid = @[code: 404, message: 'Not found'];
```

### 11.1.3 Creating Error Values

Error values are created using the `@` operator.

**Syntax:** `@errorValue`

**Examples:**
```walnut
/* String error */
@'Error message'

/* Structured error */
@[code: 404, message: 'Not found', details: 'The requested resource does not exist']

/* Error with type */
@ValidationError[field: 'email', reason: 'Invalid format']

/* Boolean error */
@true

/* Integer error */
@404
```

## 11.2 Unconditional Early Return

### 11.2.1 The => Operator

The `=>` operator causes an immediate return from the current function or scoped expression.

**Syntax:** `=> returnValue`

**Examples:**
```walnut
/* Early return from function */
processValue = ^x: Integer => String ::
    ?when(x < 0) {
        => 'Negative'  /* Early return */
    };
    ?when(x == 0) {
        => 'Zero'      /* Early return */
    };
    'Positive'         /* Normal return */
;

/* Early return from scoped expression */
result = :: {
    x = 42;
    ?when(x > 10) {
        => 'Large'     /* Exits the :: block */
    };
    'Small'
};
```

### 11.2.2 Return Values

The `=>` operator returns the value of the expression that follows it.

**Example:**
```walnut
findUser = ^id: Integer => Result<User, String> ::
    ?when(id <= 0) {
        => @'Invalid ID'  /* Return error */
    };

    user = lookupUser(id);
    ?when(user == null) {
        => @'User not found'  /* Return error */
    };

    user  /* Return success value */
;
```

## 11.3 Conditional Error Return: ?noError

### 11.3.1 Basic Usage

The `?noError(expr)` operator checks if `expr` is an error value. If it is, it immediately returns that error from the current function. Otherwise, it evaluates to the success value.

**Syntax:** `?noError(expression)`

**Examples:**
```walnut
/* Without ?noError */
processData = ^input: String => Result<Integer, String> :: {
    parsed = parse(input);  /* Returns Result<Integer, String> */
    ?whenIsError(parsed) {
        => parsed  /* Return the error */
    };
    validated = validate(parsed);  /* Returns Result<Integer, String> */
    ?whenIsError(validated) {
        => validated  /* Return the error */
    };
    validated
};

/* With ?noError */
processData = ^input: String => Result<Integer, String> :: {
    parsed = ?noError(parse(input));      /* Returns error or unwraps */
    validated = ?noError(validate(parsed));  /* Returns error or unwraps */
    validated
};
```

### 11.3.2 Error Propagation

`?noError` automatically propagates errors up the call stack.

**Example:**
```walnut
readFile = ^path: String => Result<String, String> ::
    /* ... */
;

parseJson = ^content: String => Result<JsonValue, String> ::
    /* ... */
;

processFile = ^path: String => Result<JsonValue, String> :: {
    content = ?noError(readFile(path));   /* Propagate read errors */
    json = ?noError(parseJson(content));  /* Propagate parse errors */
    json
};
```

### 11.3.3 Chaining with ?noError

Multiple operations can be chained with `?noError`.

**Example:**
```walnut
pipeline = ^input: String => Result<Integer, String> :: {
    step1 = ?noError(parseInput(input));
    step2 = ?noError(validateInput(step1));
    step3 = ?noError(transformInput(step2));
    step4 = ?noError(finalizeInput(step3));
    step4
};
```

## 11.4 The => Shorthand Operator

### 11.4.1 Method Call with Error Check

The `=>` operator can be used as a shorthand for method calls with `?noError`.

**Syntax:** `target => methodName(parameter)`

**Equivalent to:** `?noError(target->methodName(parameter))`

**Examples:**
```walnut
/* Using ?noError explicitly */
result = ?noError(file->read());

/* Using => shorthand */
result = file => read();

/* Chaining */
content = file => read() => parse() => validate();

/* Equivalent to */
content = {
    temp1 = ?noError(file->read());
    temp2 = ?noError(temp1->parse());
    ?noError(temp2->validate())
};
```

### 11.4.2 Practical Example

```walnut
processOrder = ^orderId: Integer => Result<Response, Error> :: {
    /* Traditional style */
    order = ?noError(database->findOrder(orderId));
    validated = ?noError(order->validate());
    processed = ?noError(validated->process());
    ?noError(processed->save());

    /* Using => shorthand */
    order = database => findOrder(orderId);
    validated = order => validate();
    processed = validated => process();
    processed => save();

    /* Fully chained */
    database => findOrder(orderId)
             => validate()
             => process()
             => save()
};
```

## 11.5 External Errors

### 11.5.1 ExternalError Type

`ExternalError` is a special sealed type used for errors from external sources (I/O, network, database, etc.).

**Definition (in core library):**
```walnut
ExternalError := $[
    errorType: String,
    originalError: Any,
    errorMessage: String
];
```

**Examples:**
```walnut
/* File read error */
fileError = @ExternalError[
    errorType: 'FileNotFound',
    originalError: null,
    errorMessage: 'File does not exist'
];

/* Database error */
dbError = @ExternalError[
    errorType: 'DatabaseError',
    originalError: rawError,
    errorMessage: 'Connection failed'
];
```

### 11.5.2 Impure Type

The `Impure<T>` type (shorthand: `*T`) represents operations that may fail with external errors.

**Type definition:**
```walnut
Impure<T> = Result<T, ExternalError>
*T = Impure<T>  /* Shorthand */
```

**Examples:**
```walnut
/* Impure operations */
readFile: ^String => Impure<String>;
queryDatabase: ^String => *Array<Record>;

/* Equivalent to */
readFile: ^String => Result<String, ExternalError>;
queryDatabase: ^String => Result<Array<Record>, ExternalError>;
```

## 11.6 Conditional External Error Return: ?noExternalError

### 11.6.1 Basic Usage

The `?noExternalError(expr)` operator checks if `expr` is an external error value. If it is, it immediately returns that error. Otherwise, it evaluates to the value (which may still be a regular error).

**Syntax:** `?noExternalError(expression)`

**Examples:**
```walnut
processFile = ^path: String => Result<String, ExternalError|String> :: {
    /* Read file (may return ExternalError) */
    content = ?noExternalError(file->read(path));

    /* Parse content (may return regular error) */
    parsed = ?noError(content->parse());

    parsed
};
```

### 11.6.2 The |> Shorthand Operator

The `|>` operator is a shorthand for method calls with `?noExternalError`.

**Syntax:** `target |> methodName(parameter)`

**Equivalent to:** `?noExternalError(target->methodName(parameter))`

**Examples:**
```walnut
/* Using ?noExternalError explicitly */
content = ?noExternalError(file->read());

/* Using |> shorthand */
content = file |> read();

/* Chaining */
data = file |> read() |> decode() |> validate();

/* Mixed with => */
result = file |> read()      /* Handle external errors */
              => parse()      /* Handle all errors */
              => validate();  /* Handle all errors */
```

### 11.6.3 Impure Operations Example

```walnut
loadUserData = ^userId: Integer => *UserData %% [~Database, ~FileSystem] :: {
    /* Read from database (impure) */
    dbRecord = %database |> query(userId);

    /* Read from file system (impure) */
    fileData = %fileSystem |> readFile(dbRecord.path);

    /* Parse (pure, may have regular errors) */
    parsed = fileData => parseJson();

    /* Construct result */
    UserData![
        id: userId,
        data: parsed
    ]
};
```

## 11.7 Error Conversion: *> Operator

### 11.7.1 Converting Errors to External Errors

The `*>` operator converts a regular error into an external error with a custom message.

**Syntax:** `expression *> message`

**Examples:**
```walnut
processData = ^input: String => *Result<Data, String> :: {
    /* Convert parse error to external error */
    parsed = (parse(input) *> 'Failed to parse input');

    /* Continue processing */
    validated = (validate(parsed) *> 'Validation failed');

    validated
};
```

### 11.7.2 Error Wrapping

```walnut
/* Wrap business logic errors as external errors */
saveUser = ^user: User => *Null :: {
    validation = validateUser(user) *> 'User validation failed';
    database |> save(user);
    null
};
```

## 11.8 Error Checking Expressions

### 11.8.1 ?whenIsError

The `?whenIsError` expression checks if a value is an error and branches accordingly.

**Syntax:**
```walnut
?whenIsError(expression) { errorBranch } ~ { successBranch }
```

**Examples:**
```walnut
result = divide(10, 0);

message = ?whenIsError(result) {
    'Division failed: ' + result->error
} ~ {
    'Result: ' + result->asString
};

/* Without else branch */
?whenIsError(result) {
    => 'Error occurred'
};
result  /* Returns original value if not error */
```

### 11.8.2 Error Value Access

When a value is known to be an error, access its error value with the `->error` method.

**Example:**
```walnut
processResult = ^r: Result<Integer, String> => String ::
    ?whenIsError(r) {
        'Error: ' + r->error
    } ~ {
        'Success: ' + r->asString
    }
;
```

## 11.9 Practical Patterns

### 11.9.1 Validation Pipeline

```walnut
validateUser = ^input: Map => Result<User, ValidationError> :: {
    email = ?noError(validateEmail(input->item('email')));
    age = ?noError(validateAge(input->item('age')));
    name = ?noError(validateName(input->item('name')));

    User![email: email, age: age, name: name]
};
```

### 11.9.2 Database Operations

```walnut
findAndUpdateUser = ^id: Integer, updates: Map => *User %% [~Database] :: {
    /* Query database (impure) */
    user = %database |> findById(id);

    /* Validate updates (pure) */
    validated = updates => validate();

    /* Update user */
    updated = user->applyUpdates(validated);

    /* Save to database (impure) */
    %database |> save(updated);

    updated
};
```

### 11.9.3 Multi-Step Processing

```walnut
processOrder = ^orderId: Integer => Result<Receipt, Error> %% [~Database, ~Payment] :: {
    /* Load order (impure) */
    order = %database |> loadOrder(orderId);

    /* Validate order (pure) */
    validated = order => validate();

    /* Calculate total (pure) */
    total = validated => calculateTotal();

    /* Process payment (impure) */
    paymentResult = %payment |> charge(total);

    /* Create receipt */
    receipt = Receipt![
        orderId: orderId,
        total: total,
        transactionId: paymentResult
    ];

    /* Save receipt (impure) */
    %database |> saveReceipt(receipt);

    receipt
};
```

### 11.9.4 Error Recovery

```walnut
loadConfig = ^path: String => Config :: {
    /* Try to load from file */
    result = file->read(path);

    ?whenIsError(result) {
        /* Use default config on error */
        => DefaultConfig
    };

    /* Parse config */
    parsed = result => parseJson();
    Config(parsed)
};
```

### 11.9.5 Nested Error Handling

```walnut
processNestedData = ^input: String => Result<Output, String> :: {
    /* Parse outer structure */
    outer = ?noError(parseOuter(input));

    /* Process each inner item */
    processed = outer->items->map(^item => Result<ProcessedItem, String> :: {
        validated = ?noError(validate(item));
        transformed = ?noError(transform(validated));
        transformed
    });

    /* Check for any errors in processed items */
    ?when(processed->contains(@_)) {
        => @'Error processing items'
    };

    Output![items: processed]
};
```

## 11.10 Error Handling Best Practices

### 11.10.1 Use Result for Expected Failures

```walnut
/* Good: Use Result for expected failures */
findUser = ^id: Integer => Result<User, String> ::
    ?when(id <= 0) {
        @'Invalid user ID'
    } ~ {
        /* lookup user */
    }
;

/* Avoid: Using null or special values */
/* findUser = ^id: Integer => User|Null */
```

### 11.10.2 Use Impure for External Operations

```walnut
/* Good: Mark external operations as impure */
readFile = ^path: String => *String ::
    /* File I/O can fail with ExternalError */
;

/* Clear: Caller knows this may have external errors */
content = file |> read();
```

### 11.10.3 Propagate Errors with => and |>

```walnut
/* Good: Use shorthand operators */
result = database => query(id)
                  => validate()
                  => transform();

/* Avoid: Verbose error checking */
/* result = ?noError(?noError(?noError(
    database->query(id))->validate())->transform()); */
```

### 11.10.4 Handle Errors at Appropriate Level

```walnut
/* Good: Handle errors where you can recover */
loadUserOrDefault = ^id: Integer => User :: {
    result = database |> findUser(id);
    ?whenIsError(result) {
        => DefaultUser
    };
    result
};

/* Good: Propagate errors when you can't recover */
strictLoadUser = ^id: Integer => *User :: {
    database |> findUser(id)
};
```

### 11.10.5 Provide Context in Errors

```walnut
/* Good: Descriptive error messages */
validateAge = ^age: Integer => Result<Integer<0..150>, String> ::
    ?when(age < 0 || age > 150) {
        @'Age must be between 0 and 150, got: ' + age->asString
    } ~ {
        age
    }
;

/* Avoid: Generic errors */
/* @'Invalid' */
```

## 11.11 Scoped Expressions and Early Returns

Early returns work within scoped expressions (`:: { ... }`).

**Example:**
```walnut
result = :: {
    x = 10;
    ?when(x > 5) {
        => 'Large'  /* Exits the scoped expression */
    };
    'Small'
};
/* result = 'Large' */
```

**Multiple levels:**
```walnut
outer = :: {
    inner = :: {
        => 'Inner return'  /* Returns from inner scope */
    };
    inner + ' continued'
};
/* outer = 'Inner return continued' */
```

## 11.11 Result Folding with Collections

When using `map` on collections where the mapping function returns a `Result` type, Walnut automatically "folds" the results. If any element produces an error, the entire operation returns that error immediately. Otherwise, it returns an array of the successful values.

### 11.11.1 Automatic Result Folding

**Example:**
```walnut
E := ();
P := (a, b, c);
Q := (d, e);

>>> {
    myFn = ^ pl: Array<P> => Result<Array<Q>, E> :: {
        pl->map(^~P => Result<Q, E> :: ?whenValueOf(p) is {
            P.a: Q.d,
            P.b: Q.e,
            ~: @E
        })
    };

    [myFn[P.a, P.b], myFn[P.b, P.c], myFn[]]->printed
};
/* Output: [[Q.d, Q.e], @E, []] */
```

**Explanation:**
- `myFn[P.a, P.b]` maps both values successfully → `[Q.d, Q.e]`
- `myFn[P.b, P.c]` encounters `P.c` which returns `@E` → entire result is `@E`
- `myFn[]` maps empty array → `[]`

This folding behavior means you don't need to manually check each result—the `map` operation automatically propagates errors for you.

### 11.11.2 Folding Behavior

```walnut
/* When all succeed */
[1, 2, 3]->map(^x => Result<Integer, String> :: x * 2);
/* Returns: [2, 4, 6] */

/* When one fails */
[1, 2, 3]->map(^x => Result<Integer, String> ::
    ?when(x == 2) { @'Error at 2' } ~ { x * 2 }
);
/* Returns: @'Error at 2' */
```

The folding stops at the first error, making it efficient and predictable.

## 11.12 Scoped Expressions and Early Returns

Early returns work within scoped expressions (`:: { ... }`).

**Example:**
```walnut
result = :: {
    x = 10;
    ?when(x > 5) {
        => 'Large'  /* Exits the scoped expression */
    };
    'Small'
};
/* result = 'Large' */
```

**Multiple levels:**
```walnut
outer = :: {
    inner = :: {
        => 'Inner return'  /* Returns from inner scope */
    };
    inner + ' continued'
};
/* outer = 'Inner return continued' */
```

## 11.13 Summary

Walnut's error handling system provides:

- **Result type** for representing success or failure
- **Early returns** with `=>` for explicit control flow
- **?noError** for automatic error propagation
- **?noExternalError** for handling external errors separately
- **Shorthand operators** (`=>`, `|>`) for concise error handling
- **Error conversion** with `*>` operator
- **Type safety** through compile-time checking
- **Explicit error types** for clear error contracts
- **Impure types** for marking side-effecting operations
- **Error checking expressions** with `?whenIsError`

This system combines the benefits of:
- **Explicit error handling** (like Result/Either in functional languages)
- **Convenient control flow** (like exceptions, but without hidden control flow)
- **Type safety** (all errors are tracked in function signatures)
- **Composability** (errors propagate naturally through pipelines)

The result is robust, maintainable error handling that makes error cases visible and forces developers to handle them appropriately.
