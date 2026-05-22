# Early Return and Error Handling Operators

This guide provides a comprehensive overview of Walnut's early return and error conversion operators, complementing the main error handling documentation in Chapter 11.

## Quick Reference

### Early Return Operators

```walnut
expr?!   /* Early return if empty */
expr@!   /* Early return if error */
expr*!   /* Early return if external error */
expr!    /* Early return if empty or error */
```

### Error Conversion Operators

```walnut
expr @?              /* Error → empty (postfix) */
expr *?              /* External error → empty (postfix) */
expr ?@ errorValue   /* Empty → error with value */
expr @* (message)    /* Error → external error with message */
expr ?* (message)    /* Empty → external error with message */
```

### Conditional Checking Operators

```walnut
?whenIsError(expr) { ... } ~ { ... }           /* Branch on error */
?whenIsEmpty(expr) { ... } ~ { ... }           /* Branch on empty */
?whenIsExternalError(expr) { ... } ~ { ... }   /* Branch on external error */
```

## Type Refinement

Each operator refines the type of an expression by removing or transforming error types:

### Early Return Operators - Type Refinement

| Operator | Input Type | Output on Success | Action on Match |
|----------|-----------|-------------------|-----------------|
| `expr?!` | `Empty \| T` | `T` | Return empty |
| `expr@!` | `Result<T, E>` | `T` | Return error |
| `expr*!` | `Result<T, E \| ExternalError>` | `Result<T, E>` | Return external error |
| `expr!` | `Result<T, E> \| Empty` | `T` | Return error or empty |

### Error Conversion Operators - Type Transformation

| Operator | Transforms | Use Case |
|----------|------------|----------|
| `expr @?` | `Result<T, E>` → `T \| Empty` | Graceful error handling |
| `expr *?` | `T*` → `T \| Empty` | Ignore external errors |
| `expr ?@ errVal` | `T \| Empty` → `Result<T, ErrType>` | Require value |
| `expr @* (msg)` | `Result<T, E>` → `T*` | Escalate to external error |
| `expr ?* (msg)` | `T \| Empty` → `T*` | Mark empty as external |

## Common Patterns

### Pattern 1: Graceful Degradation

Use `@?` to convert errors to empty, then provide a fallback:

```walnut
getUserOrDefault = ^id: Integer => User :: {
    user = fetchUser(id) @? ?? DefaultUser;
    user
};
```

### Pattern 2: Validation Pipeline

Use early returns to propagate validation errors:

```walnut
validateAndProcess = ^input: String => Result<Data, String> :: {
    /* Parse - returns if error */
    parsed = input->parse()@!;
    
    /* Validate - returns if error */
    validated = validate(parsed)@!;
    
    /* Process */
    process(validated)
};
```

### Pattern 3: Required Values

Use `?@` to convert empty values into errors with a descriptive message:

```walnut
requireField = ^value: ?String => Result<String, String> ::
    value ?@ 'Field is required';
```

### Pattern 4: Error Escalation

Use `@*` to convert business logic errors into external errors with context:

```walnut
wrapWithContext = ^operation: ^=> Result<Data, String> => Data* :: {
    result = operation();
    result @* ('Operation failed')
};
```

### Pattern 5: Conditional Handling

Use conditional operators for explicit branching:

```walnut
handleResult = ^value: Result<Integer, String> => String :: {
    ?whenIsError(value) {
        => 'Error: ' + value->error
    };
    
    ?when(value < 0) {
        => 'Negative value'
    };
    
    'Valid: ' + value->asString
};
```

## Edge Cases

### Handling Optional Results

When working with optional result types (`Result<T, E>?`):

```walnut
process = ^value: Result<Integer, String>? => Integer :: {
    /* First remove empty */
    non_empty = value?!;
    /* Then remove error */
    non_error = non_empty@!;
    non_error * 2
};
```

### Distinguishing Error Types

When a value might have multiple error types:

```walnut
handleErrors = ^value: Result<Integer, String> => Integer :: {
    /* Use early return for regular errors */
    validated = value@!;
    
    /* Further validation if needed */
    ?when(validated < 0) {
        => 0
    };
    
    validated
};
```

### External Error Handling

For operations that produce external errors (I/O, network):

```walnut
readAndProcess = ^path: String => String :: {
    /* Handle external errors from file I/O */
    content = readFile(path)*!;
    
    /* Parse content (may have regular errors) */
    data = parseJson(content)@!;
    
    data->asString
};
```

## Operator Combinations

### Sequential Early Returns

```walnut
/* All errors return immediately */
process = ^a: ?String, b: Result<Integer, String> => Integer :: {
    a_val = a?!;
    b_val = b@!;
    a_val->length + b_val
};
```

### Conditional + Early Return

```walnut
/* Combine conditions with early returns */
validate = ^value: Integer => Result<Integer, String> :: {
    ?when(value < 0) {
        => @'Negative'
    };
    ?when(value > 100) {
        => @'Too large'
    };
    value
};
```

### Multiple Conversions

```walnut
/* Chain conversions for complex transformations */
transform = ^input: ?Result<String, Integer> => ?String :: {
    /* Remove empty */
    non_empty = input?!;
    /* Convert error to empty */
    optional = non_empty @?;
    optional
};
```

## Best Practices

1. **Use early returns for error propagation**: Prefer `?!`, `@!`, `*!` for quick propagation
2. **Use conditional operators for explicit branching**: Use `?whenIsError`, etc. when logic depends on state
3. **Convert near boundaries**: Apply conversion operators (`@?`, `?@`, etc.) when crossing API boundaries
4. **Escalate appropriately**: Use `@*` when converting business errors to external errors
5. **Provide fallbacks**: Use `??` with conversions for graceful degradation

## Testing Operators

The test suites cover:

**Early return operators:**
- `EarlyReturnEmptyExpressionTest` — `expr?!`
- `EarlyReturnErrorExpressionTest` — `expr@!`
- `EarlyReturnExternalErrorExpressionTest` — `expr*!`
- `EarlyReturnUnhappyExpressionTest` — `expr!`

**Error conversion operators:**
- `ErrorAsEmptyExpressionTest` — `expr @?`
- `ExternalErrorAsEmptyExpressionTest` — `expr *?`
- `EmptyAsErrorExpressionTest` — `expr ?@ errVal`
- `ErrorAsExternalTest` — `expr @* (msg)`
- `EmptyAsExternalTest` — `expr ?* (msg)`

**Conditional checking expressions:**
- `MatchErrorExpressionTest` — `?whenIsError`
- `MatchEmptyExpressionTest` — `?whenIsEmpty`
- `MatchExternalErrorExpressionTest` — `?whenIsExternalError`

See the test files in `tests/Walnut/Lang/Almond/Engine/Implementation/Expression/` and `tests/Walnut/Lang/Almond/Engine/NativeCode/Any/` for comprehensive examples.
