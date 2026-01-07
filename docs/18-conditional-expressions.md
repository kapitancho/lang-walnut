# Conditional Expressions

## Overview

Walnut provides five conditional expressions for control flow and pattern matching, plus two early return expressions for error handling. All conditional expressions have similar syntax patterns and support type refinement to enhance type safety within branches.

**Conditional Expressions:**
- `?when` - If-Then-Else conditional
- `?whenValueOf` - Value matching
- `?whenTypeOf` - Type matching
- `?whenIsTrue` - Boolean condition matching
- `?whenIsError` - Error checking

**Early Return Expressions:**
- `?noError` - Early return on error
- `?noExternalError` - Early return on external error

## `?when` - If-Then-Else

The `?when` expression evaluates a condition and executes one of two branches based on the result.

### Syntax

**With else branch:**
```walnut
?when(condition) { thenExpr } ~ { elseExpr }
```

**Without else branch:**
```walnut
?when(condition) { thenExpr }
```

### Behavior

1. The `condition` is evaluated and converted to `Boolean` via the `->asBoolean` method
2. If the result is `true`, `thenExpr` is evaluated and returned
3. If the result is `false`:
   - With else branch: `elseExpr` is evaluated and returned
   - Without else branch: `null` is returned

### Examples

```walnut
/* Simple if-then-else */
?when(x > 0) { 1 } ~ { -1 }

/* Without else branch (returns null if false) */
?when(x > 0) { 1 }

/* From demo-all.nut */
matchIfThenElse: ?when('condition') { 'then' } ~ { 'else' }
matchIfThen: ?when('condition') { 'then' }

/* Early return pattern */
return: ?when(0) { => 'return' }
```

### Real-World Examples

```walnut
/* From core.nut - Validation in constructor */
IntegerRange := #[minValue: Integer|MinusInfinity, maxValue: Integer|PlusInfinity] @ InvalidRange ::
    ?whenTypeOf(#) { `[minValue: Integer, maxValue: Integer]:
        ?when (#minValue > #maxValue) { => @InvalidRange }
    };

/* Complex validation */
RealNumberInterval := #[
    start: MinusInfinity|RealNumberIntervalEndpoint,
    end: PlusInfinity|RealNumberIntervalEndpoint
] @ InvalidRange :: ?whenTypeOf(#) {
    `[start: RealNumberIntervalEndpoint, end: RealNumberIntervalEndpoint]:
        ?when (
            {#.start.value > #.end.value} ||
            {#.start.value == #.end.value && {!{#.start.inclusive} || !{#.end.inclusive}}}
        ) { => @InvalidRange }
};
```

### Type Refinement

The `?when` expression does not provide automatic type refinement. For type-based refinement, use `?whenTypeOf`.

### Return Type

- **With else branch**: Union of `thenExpr` and `elseExpr` types
- **Without else branch**: Union of `thenExpr` type and `Null`

## `?whenValueOf` - Value Matching

The `?whenValueOf` expression matches the value of an expression against a set of values, similar to a switch statement.

### Syntax

```walnut
?whenValueOf(expr) {
    matchExpr1: resultExpr1,
    matchExpr2: resultExpr2,
    ...
    matchExprN: resultExprN,
    ~: defaultResultExpr
}
```

### Behavior

1. `expr` is evaluated once
2. The result is matched against each `matchExpr` value in order
3. The first matching value's corresponding `resultExpr` is evaluated and returned
4. If no match is found:
   - With default branch (`~`): `defaultResultExpr` is evaluated and returned
   - Without default branch: `null` is returned

### Examples

```walnut
/* Basic value matching */
?whenValueOf(x) {
    42: x + 1,
    1000: 2,
    ~: 0
}

/* From demo-all.nut */
matchValue: ?whenValueOf('value') {
    'value': 'then 1',
    'other value': 'then 2',
    ~: 'default'
}

/* Enumeration to integer cast */
Suit ==> Integer ::
    ?whenValueOf($) {
        Suit.Clubs: 1,
        Suit.Diamonds: 2,
        Suit.Hearts: 3,
        Suit.Spades: 4
    };
```

### Real-World Examples

```walnut
/* From datetime.nut - Date validation */
Date := #[year: Integer, month: Integer<1..12>, day: Integer<1..31>] @ InvalidDate :: {
    ?whenValueOf(#day) {
        31: ?whenTypeOf(#month) {
            `Integer[2, 4, 6, 9, 11]: => @InvalidDate,
            ~: null
        },
        30: ?whenTypeOf(#month) {
            `Integer[2]: => @InvalidDate,
            ~: null
        },
        29: ?whenTypeOf(#month) {
            `Integer[2]: ?whenIsTrue {
                {#year % 4} > 0: => @InvalidDate,
                {#year % 100} == 0: ?whenIsTrue {
                    {#year % 400} > 0: => @InvalidDate,
                    ~: null
                },
                ~: null
            },
            ~: null
        },
        ~: null
    }
};

/* From datetime.nut - Handling zero-padded strings */
dropZeroes = ^s: String => Result<Integer, NotANumber> :: ?whenValueOf(s) {
    '00': 0,
    ~: s->trimLeft('0')->asInteger
};

/* From http/all.test.nut - Service method dispatch */
SpecialService->invoke(^param: String => Result<String, SpecialServiceError>) ::
    ?whenValueOf(param) {
        '': @SpecialServiceError['Empty string'],
        ~: param->reverse
    };

/* From db/sql/quoter-mysql.nut - Boolean to SQL string */
`Boolean: ?whenValueOf(v) { true: '1', false: '0' }

/* From db/sql/query-builder.nut - Enum to SQL */
SqlFieldExpressionOperation ==> SqlString :: ?whenValueOf($) {
    SqlFieldExpressionOperation.Equal: '=',
    SqlFieldExpressionOperation.NotEqual: '!=',
    SqlFieldExpressionOperation.LessThan: '<',
    SqlFieldExpressionOperation.GreaterThan: '>',
    ~: ''
};
```

### Type Refinement

When `expr` is a variable, its type is automatically refined in each `resultExpr` branch based on the corresponding `matchExpr` type.

```walnut
/* Type refinement example */
?whenValueOf(x) {
    42: x + 1,  /* Compiler knows x is 42 (Integer) */
    ~: 0
}
```

### Return Type

- **With default branch**: Union of all `resultExpr` types
- **With default branch when all possible values are covered**: Union of all `resultExpr` types
- **Without default branch**: Union of all `resultExpr` types and `Null`

## `?whenTypeOf` - Type Matching

The `?whenTypeOf` expression matches the type of an expression against a set of types, enabling powerful pattern matching with type refinement.

### Syntax

```walnut
?whenTypeOf(expr) {
    matchExpr1: resultExpr1,
    matchExpr2: resultExpr2,
    ...
    matchExprN: resultExprN,
    ~: defaultResultExpr
}
```

### Behavior

1. `expr` is evaluated once
2. The type of the result is matched against each `matchExpr` type in order
3. The first type that the result is a subtype of matches
4. The corresponding `resultExpr` is evaluated and returned
5. If no match is found:
   - With default branch (`~`): `defaultResultExpr` is evaluated and returned
   - Without default branch: `null` is returned

### Type Expression Syntax

Type expressions in `matchExpr` can use:
- ``TypeExpression` - Type expression syntax
- `` `TypeName `` - Shorter syntax for type literals
- Type ranges and subsets

```walnut
/* Type expression syntax */
`Integer<1..>
`Real
`[minValue: Integer, maxValue: Integer]
type[minValue: Integer, maxValue: Integer] /* shorter form */
`[Integer, Integer]
type[Integer, Integer] /* shorter form */

/* Type literal syntax - shorthand */
`Integer
`String
`String['type']
`[Integer, Integer]
`[year: Integer, month: Integer<1..12>, day: Integer<1..31>]
```

### Examples

```walnut
/* Basic type matching */
?whenTypeOf(x) {
    `Integer<1..>: 1,
    `Real: x + 3.14,
    ~: 0
}

/* From demo-all.nut */
matchType: ?whenTypeOf('type') {
    `String['type']: 'then 1',
    `String['other type']: 'then 2',
    ~: 'default'
}

/* Enumeration method */
Suit->getSuitColor(=> String['black', 'red']) ::
    ?whenTypeOf($) {
        `Suit[Clubs, Spades]: 'black',
        `Suit[Diamonds, Hearts]: 'red'
    };
```

### Real-World Examples

```walnut
/* From core.nut - Type-specific range creation */
IntegerRange := #[minValue: Integer|MinusInfinity, maxValue: Integer|PlusInfinity] @ InvalidRange ::
    ?whenTypeOf(#) {
        `[minValue: Integer, maxValue: Integer]:
            ?when (#minValue > #maxValue) { => @InvalidRange }
    };

/* From datetime.nut - Polymorphic cast */
JsonValue ==> Date @ InvalidDate :: {
    ?whenTypeOf($) {
        `String: $->asDate,
        `[Integer, Integer<1..12>, Integer<1..31>]: Date($),
        `[year: Integer, month: Integer<1..12>, day: Integer<1..31>]: Date($),
        ~: @InvalidDate
    }
};

/* From datetime.nut - String parsing with refinement */
String ==> Date @ InvalidDate :: {
    pieces = $->split('-');
    ?whenTypeOf(pieces) {
        type[String<4>, String<2>, String<2>]: {
            dateValue = [
                year: pieces.0->asInteger,
                month: pieces.1->trimLeft('0')->asInteger,
                day: pieces.2->trimLeft('0')->asInteger
            ];
            ?whenTypeOf(dateValue) {
                type[year: Integer, month: Integer<1..12>, day: Integer<1..31>]: Date(dateValue),
                ~: @InvalidDate
            }
        },
        ~: @InvalidDate
    }
};

/* From db/sql/quoter-mysql.nut - Type-based SQL quoting */
quoteValue: ^v: String|Integer|Real|Boolean|Null => String :: ?whenTypeOf(v) {
    `String: {'\'' + v->replace['\'', '\\\''] + '\''},
    `Integer: v->asString,
    `Real: v->asString,
    `Boolean: ?whenValueOf(v) { true: '1', false: '0' },
    `Null: 'NULL'
};

/* From db/sql/query-builder.nut - Array handling */
SqlAndExpression ==> SqlString :: ?whenTypeOf($expressions) {
    `Array<SqlStringExpression, 0>: SqlString(''),
    ~: $expressions->map(^e: SqlStringExpression => String :: e->asString)->combineAsString(' AND ')
};

/* From http/autowire.nut - Response type handling */
?whenTypeOf(httpResponse) {
    `{HttpResponse}: httpResponse,
    `Type<Function>: runHandler(httpResponse),
    ~: @UnknownHandlerReturnType[httpResponse->type]
}

/* From http/router.nut - Optional value handling */
?whenTypeOf(kv) {
    `HttpLookupRouterPath: run[request: withUpdatedRequestPath(kv.path), type: kv.type],
    ~: #handler->shape(`HttpRequestHandler)(request)
}
```

### Type Refinement

When `expr` is a variable, its type is automatically refined in each `resultExpr` branch based on the corresponding `matchExpr` type. This refinement enables type-safe operations that would otherwise be impossible.

```walnut
/* Type refinement enables operation */
?whenTypeOf(x) {
    `Real: x + 3.14,  /* Compiler knows x is Real, so addition is valid */
    ~: 0
}

/* Complex refinement example */
?whenTypeOf(pieces) {
    type[String<4>, String<2>, String<2>]: {
        /* Compiler knows pieces is a 3-element tuple */
        /* with specific string length constraints */
        pieces.0->asInteger  /* Safe access to element 0 */
    },
    ~: @InvalidDate
}
```

### Exhaustiveness Checking

If there is no default branch (`~`), the compiler checks if all possible types are covered by the `matchExpr` branches.
When all cases are exhaustively handled, the return type does not include `Null`.

```walnut
/* Exhaustive matching (no default needed if all cases covered) */
?whenTypeOf(result) {
    `Integer: result + 1,
    `String: result->length
}  /* Return type: Integer (no Null because exhaustive) */

/* Non-exhaustive (needs default or returns Null) */
?whenTypeOf(result) {
    `Integer: result + 1
}  /* Return type: Integer|Null */
```

### Return Type

- **Exhaustive matching**: Union of all `resultExpr` types
- **Non-exhaustive without default**: Union of all `resultExpr` types and `Null`
- **With default branch**: Union of all `resultExpr` types

## `?whenIsTrue` - Boolean Condition Matching

The `?whenIsTrue` expression evaluates multiple conditions in order and executes the first branch whose condition is true.

### Syntax

```walnut
?whenIsTrue {
    matchExpr1: resultExpr1,
    matchExpr2: resultExpr2,
    ...
    matchExprN: resultExprN,
    ~: defaultResultExpr
}
```

### Behavior

1. `matchExpr` values are evaluated from top to bottom
2. Each `matchExpr` is converted to `Boolean` via `->asBoolean`
3. Once a condition evaluates to `true`, evaluation stops
4. The corresponding `resultExpr` is evaluated and returned
5. If no condition is `true`:
   - With default branch (`~`): `defaultResultExpr` is evaluated and returned
   - Without default branch: `null` is returned

### Examples

```walnut
/* Basic condition matching */
?whenIsTrue {
    x > 0: 1,
    x < 0: -1,
    ~: 0
}

/* From demo-all.nut */
matchTrue: ?whenIsTrue {
    'then 1': 'then 1',
    'then 2': 'then 2',
    ~: 'default'
}
```

### Real-World Examples

```walnut
/* From datetime.nut - Leap year calculation */
`Integer[2]: ?whenIsTrue {
    {#year % 4} > 0: => @InvalidDate,
    {#year % 100} == 0: ?whenIsTrue {
        {#year % 400} > 0: => @InvalidDate,
        ~: null
    },
    ~: null
}

/* From db/orm/xorm-repository.nut - Error handling with conditions */
`Error<DatabaseQueryFailure>: ?whenIsTrue {
    $errorMessage->contains('already exists'): @IdAlreadyExists[id],
    ~: result
}
```

### Type Refinement

The `?whenIsTrue` expression does not provide automatic type refinement. Use `?whenTypeOf` when type refinement is needed.

### Return Type

- **With default branch**: Union of all `resultExpr` types
- **Without default branch**: Union of all `resultExpr` types and `Null`

## `?whenIsError` - Error Checking

The `?whenIsError` expression checks if an expression evaluates to an `Error` type and executes different branches accordingly.

### Syntax

**With else branch:**
```walnut
?whenIsError(expr) { thenExpr } ~ { elseExpr }
```

**Without else branch:**
```walnut
?whenIsError(expr) { thenExpr }
```

### Behavior

1. `expr` is evaluated once
2. The result is checked if it is of type `Error`
3. If it is an error:
   - `thenExpr` is evaluated and returned
4. If it is not an error:
   - With else branch: `elseExpr` is evaluated and returned
   - Without else branch: the value of `expr` is returned unmodified

### Examples

```walnut
/* With else branch */
?whenIsError(x) { 0 } ~ { x->length }

/* Without else branch (returns x if not error) */
?whenIsError(x) { 1 }

/* From demo-all.nut */
matchIsErrorElse: ?whenIsError('condition') { 'then' } ~ { 'else' }
matchIsError: ?whenIsError('condition') { 'then' }
```

### Real-World Examples

```walnut
/* From http/all.test.nut - Error handling in HTTP handler */
SpecialHandler ==> HttpRequestHandler %% [~HttpResponseBuilder, ~SpecialService] ::
    ^request: {HttpRequest} => {HttpResponse} :: {
        message = %specialService(request->shape(`HttpRequest).body->asString);
        ?whenIsError(message) {
            message->error
        } ~ {
            %httpResponseBuilder(200)->withBody('<h1>' + message + '</h1>')
        }
    };

/* From http/router.nut - Optional method matching */
requestMethod == ?whenIsError(pathMethod) { requestMethod }

/* From tpl.nut - Providing default value */
?whenIsError($reason) { 'unknown' }

/* From http/autowire/response-helper.nut - JSON handling */
?whenIsError(jsonValue) { jsonValue } ~ {
    %httpResponseBuilder(statusCode)->withBody(jsonValue->asPrettyJsonString)
}
```

### Type Refinement

When `expr` is a variable, its type is automatically refined in both branches:

**In `thenExpr` (error branch):**
- `Result<T, E>` is refined to `Error<E>`
- `Any` is refined to `Error<Any>`
- Other types remain unchanged

**In `elseExpr` (success branch):**
- `Result<T, E>` is refined to `T`
- Other types remain unchanged

```walnut
/* Type refinement example */
x: Result<String, SomeError> = ...;
?whenIsError(x) {
    0  /* x is Error<SomeError> here */
} ~ {
    x->length  /* x is String here, ->length is valid */
}
```

### Return Type

- **With else branch**: Union of `thenExpr` type and `elseExpr` type
- **Without else branch**: Union of `thenExpr` type and refined (non-error) `expr` type

## `?noError` - Early Return on Error

The `?noError` expression checks if an expression is an error and immediately returns from the containing function if it is.

### Syntax

```walnut
?noError(expr)
```

### Behavior

1. `expr` is evaluated
2. If the result is of type `Error`, it is immediately returned from the containing function
3. Otherwise, the expression evaluates to the non-error result

### Examples

```walnut
/* Basic usage */
?noError(x->sqrt);

/* From demo-all.nut */
noError: ?noError('no error')

/* From datetime.nut */
DateAndTime[?noError(Date[$.0, $.1, $.2]), ?noError(Time[$.3, $.4, $.5])]
```

### Type Refinement

When `expr` is of type `Result<T, E>`:
- The resulting expression type is refined to `T`
- The function return type must declare that it can return `Error<E>`

```walnut
/* Function example */
calculateDistance = ^x: Real => Result<Real, NotANumber> :: {
    sqrt = ?noError(x->sqrt);  /* sqrt is refined to Real */
    sqrt * 2
};
```

### Shorthand Method Call Syntax

The `=>` operator is shorthand for calling a method and wrapping it in `?noError`:

```walnut
/* These are equivalent: */
result = ?noError(object->method)
result = object=>method
```

### Use Cases

- Simplifying error propagation in functions
- Early exit on validation failures
- Chaining operations that may fail

## `?noExternalError` - Early Return on External Error

The `?noExternalError` expression checks if an expression is an `Error` with a value of type `ExternalError` and immediately returns from the containing function if it is.

### Syntax

```walnut
?noExternalError(expr)
```

### Behavior

1. `expr` is evaluated
2. If the result is of type `Error` AND the error value is of type `ExternalError`, it is immediately returned from the containing function
3. Otherwise, the expression evaluates to the result (which may still be an error of another type)

### Examples

```walnut
/* Basic usage */
?noExternalError(dbQuery->execute);

/* From demo-all.nut */
noExternalError: ?noExternalError('no external error')
```

### Type Refinement

When `expr` is of type `Result<T, E|ExternalError>`:
- The resulting expression type is refined to `Result<T, E>`
- The function return type must declare that it can return `Error<ExternalError>`

### Shorthand Type Syntax

The type `Result<T, ExternalError>` can be written using shorthand:
- `Impure<T>` - Long form
- `*T` - Short form

This also works for result types containing other error types:
- `Result<T, E|ExternalError>` = `Impure<Result<T, E>>` = `*Result<T, E>`

### Shorthand Method Call Syntax

The `|>` operator is shorthand for calling a method and wrapping it in `?noExternalError`:

```walnut
/* These are equivalent: */
result = ?noExternalError(object->method)
result = object|>method
```

### Use Cases

- Separating external errors (I/O, network, database) from domain errors
- Allowing external errors to propagate while handling domain errors locally
- Working with impure operations that may fail due to external factors

## Exhaustiveness Checking

The Walnut compiler performs exhaustiveness checking on conditional expressions to ensure all possible cases are handled. This is particularly important for `?whenTypeOf` and `?whenValueOf` expressions.

### Impact on Return Type

When exhaustiveness checking determines that all cases are covered:
- The return type does NOT include `Null`
- No default branch is required

When not all cases are covered:
- The return type includes `Null` (unless a default branch is provided)
- A default branch (`~`) can be added to handle remaining cases

### Examples

```walnut
/* Exhaustive enumeration matching */
Suit := (Clubs, Diamonds, Hearts, Spades);
color = ?whenValueOf(suit) {
    Suit.Clubs: 'black',
    Suit.Diamonds: 'red',
    Suit.Hearts: 'red',
    Suit.Spades: 'black'
};  /* Return type: String['black', 'red'] (no Null) */

/* Non-exhaustive matching */
result = ?whenValueOf(x) {
    42: 'the answer',
    0: 'zero'
};  /* Return type: String|Null */

/* With default branch */
result = ?whenValueOf(x) {
    42: 'the answer',
    0: 'zero',
    ~: 'other'
};  /* Return type: String (no Null because of default) */
```

## Type Refinement Summary

Type refinement allows the compiler to narrow types within conditional branches, enabling type-safe operations that would otherwise be impossible.

### Refinement by Expression Type

| Expression | Variable Refined | How |
|------------|------------------|-----|
| `?when` | No | No type refinement |
| `?whenValueOf` | Yes | Based on matched value's type |
| `?whenTypeOf` | Yes | Based on matched type |
| `?whenIsTrue` | No | No type refinement |
| `?whenIsError` | Yes | Error vs success type split |

### Refinement Examples

**Value-based refinement:**
```walnut
?whenValueOf(x) {
    42: x + 1,      /* x is refined to Integer with value 42 */
    "hello": x->length,  /* x is refined to String with value "hello" */
    ~: 0
}
```

**Type-based refinement:**
```walnut
?whenTypeOf(x) {
    `Integer<1..>: x * 2,     /* x is refined to Integer<1..> */
    `String: x->length,        /* x is refined to String */
    ~: 0
}
```

**Error-based refinement:**
```walnut
x: Result<String, SomeError> = ...;
?whenIsError(x) {
    x->errorMessage  /* x is Error<SomeError> */
} ~ {
    x->length        /* x is String */
}
```

## Best Practices

### Choosing the Right Conditional

1. **Use `?when`** for:
   - Simple boolean conditions
   - If-then-else logic without type refinement

2. **Use `?whenValueOf`** for:
   - Matching against specific values
   - Switch-like behavior
   - Enumeration to value conversion

3. **Use `?whenTypeOf`** for:
   - Type-based dispatch
   - Polymorphic behavior
   - Complex pattern matching with type refinement

4. **Use `?whenIsTrue`** for:
   - Multiple independent boolean conditions
   - Guard clause patterns
   - Ordered condition evaluation

5. **Use `?whenIsError`** for:
   - Error handling in specific contexts
   - Transforming errors to values
   - Conditional error processing

6. **Use `?noError`** for:
   - Propagating errors up the call stack
   - Simplifying error handling chains
   - Early exit on error

7. **Use `?noExternalError`** for:
   - Separating external from domain errors
   - Working with impure operations
   - Database, file, or network operations

### Default Branch Considerations

Always consider whether a default branch is needed:

```walnut
/* Exhaustive - no default needed */
?whenValueOf(suit) {
    Suit.Clubs: 1,
    Suit.Diamonds: 2,
    Suit.Hearts: 3,
    Suit.Spades: 4
}

/* Non-exhaustive - default recommended */
?whenValueOf(statusCode) {
    200: 'OK',
    404: 'Not Found',
    ~: 'Unknown'  /* Good practice */
}
```

### Nesting Conditionals

Conditionals can be nested for complex logic:

```walnut
/* From datetime.nut - Complex validation */
Date := #[year: Integer, month: Integer<1..12>, day: Integer<1..31>] @ InvalidDate :: {
    ?whenValueOf(#day) {
        31: ?whenTypeOf(#month) {
            `Integer[2, 4, 6, 9, 11]: => @InvalidDate,
            ~: null
        },
        29: ?whenTypeOf(#month) {
            `Integer[2]: ?whenIsTrue {
                {#year % 4} > 0: => @InvalidDate,
                {#year % 100} == 0: ?whenIsTrue {
                    {#year % 400} > 0: => @InvalidDate,
                    ~: null
                },
                ~: null
            },
            ~: null
        },
        ~: null
    }
};
```

## Common Patterns

### Result Type Handling

```walnut
/* Pattern 1: Transform error or use value */
result = ?whenIsError(operation()) {
    @DefaultError
} ~ {
    operation()->transform
};

/* Pattern 2: Early return on error */
calculate = ^x: Real => Result<Real, Error> :: {
    validated = ?noError(x->validate);
    processed = ?noError(validated->process);
    processed->format
};

/* Pattern 3: Separate external errors */
queryDb = ^id: Integer => *Result<User, NotFound> :: {
    connection = ?noExternalError(db->connect);
    result = ?noExternalError(connection->query(id));
    ?whenIsError(result) {
        @NotFound
    } ~ {
        result
    }
};
```

### Type-Based Dispatch

```walnut
/* Pattern: Polymorphic processing */
process = ^value: Integer|String|Boolean => String :: ?whenTypeOf(value) {
    `Integer: value->asString + ' is a number',
    `String: value + ' is a string',
    `Boolean: ?when(value) { 'true' } ~ { 'false' }
};
```

### Enumeration Patterns

```walnut
/* Pattern 1: Enumeration to string */
Color := (Red, Green, Blue);
Color ==> String :: ?whenValueOf($) {
    Color.Red: 'red',
    Color.Green: 'green',
    Color.Blue: 'blue'
};

/* Pattern 2: Subset checking */
Color->isPrimary(=> Boolean) :: ?whenTypeOf($) {
    `Color[Red, Green, Blue]: true,
    ~: false
};
```

## Summary

Walnut's conditional expressions provide powerful pattern matching and control flow capabilities:

- **`?when`** - Simple if-then-else conditionals
- **`?whenValueOf`** - Value matching with type refinement
- **`?whenTypeOf`** - Type matching with sophisticated refinement
- **`?whenIsTrue`** - Multiple boolean condition evaluation
- **`?whenIsError`** - Error-specific conditional handling
- **`?noError`** - Early return error propagation
- **`?noExternalError`** - External error separation

All conditional expressions support type refinement where applicable, enabling type-safe operations within branches. Exhaustiveness checking ensures all cases are handled, and the compiler uses this information to refine return types accurately.
