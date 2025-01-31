# Conditional expressions
There are five conditional expressions with similar syntax and two early 
return expressions for handling error values.

## ?whenValueOf
Match the value of an expression against a set of values.

### General syntax:
```walnut
?whenValueOf(expr) is { 
    matchExpr1: resultExpr1,
    matchExpr2: resultExpr2,
    ...
    matchExprN: resultExprN,
    ~: defaultResultExpr
}
```

### Example:
```walnut
?whenValueOf(x) is { 42: x + 1, 1000: 2, ~: 0 }; /* ~ is the default branch */
```

### How it works:
`expr` is evaluated and the result is matched against the `matchExpr` values.
In case the values are equal, the corresponding `resultExpr` is evaluated 
and this is the final expression value.

When none of the `matchExpr` values match the `expr` value, the `defaultResultExpr` is evaluated.
In case there is no `defaultResultExpr`, the result of the whole expression is `null`.

### Type refinement:
In case the `expr` is a variable, in all `resultExpr` branches its type is automatically 
refined based on the type of the corresponding `matchExpr` value. In the example above, `x + 1` is possible
because the compiler knows that `x` is 42 and therefore an integer.

## ?whenTypeOf
Match the type of an expression against a set of types.

### General syntax:
```walnut
?whenTypeOf(expr) is { 
    matchExpr1 : resultExpr1,
    matchExpr2 : resultExpr2,
    ...
    matchExprN : resultExprN,
    ~: defaultResultExpr
}
```

### Example:
```walnut
?whenTypeOf(x) is {
    type{Integer<1..>} : 1,
    type{Real} : x + 3.14,
    ~ : 0 /* ~ is the default branch */
};
```

### How it works:
`expr` is evaluated and the type of the result is matched against the `matchExpr` values.
In case the result type is a subtype of the type specified in `matchExpr`, 
the corresponding `resultExpr` is evaluated and this is the final expression value.

When none of the `matchExpr` types works, the `defaultResultExpr` is evaluated.
In case there is no `defaultResultExpr`, the result of the whole expression is `null`.

### Type refinement:
In case the `expr` is a variable, in all `resultExpr` branches its type is automatically
refined based on the corresponding `matchExpr` type. In the example above, `x + 3.14` is possible
because the compiler knows that `x` is of type `Real`.

## ?whenIsTrue
Match expressions against the value `true`.

### General syntax:
```walnut
?whenIsTrue { 
    matchExpr1 : resultExpr1,
    matchExpr2 : resultExpr2,
    ...
    matchExprN : resultExprN,
    ~: defaultResultExpr
}
```

### Example:
```walnut
?whenIsTrue { x > 0: 1, x < 0: -1, ~: 0 }; /* ~ is the default branch */
```

### How it works:
The `matchExpr` values are evaluated from top to bottom and converted to `Boolean`.
Once the converted value is `true` the evaluation stops and the corresponding `resultExpr` is evaluated.

When none of the `matchExpr` values are `true`, the `defaultResultExpr` is evaluated.
In case there is no `defaultResultExpr`, the result of the whole expression is `null`.

### Type refinement:
There is no type refinement for `?whenIsTrue`.


## ?when
Match an expression and evaluate one of the two branches.

### General syntax:
```walnut
?when(expr) { thenExpr } ~ { elseExpr }
```
or
```walnut
?when(expr) { thenExpr }
```

### Example:
```walnut
?when (x > 0) { 1 }; /* no else branch means 'null' */

?when (x > 0) { 1 } ~ { -1 };
```

### How it works:
The `expr` is evaluated and converted to `Boolean`.
In case the converted value is `true` then `thenExpr` is evaluated and this is the result.
Otherwise, the `elseExpr` is evaluated and used as the result. When there is no `elseExpr`, the result is `null`.

### Type refinement:
There is no type refinement for `?when`.

## ?whenIsError
Check if an expression is of type `Error` and evaluate one of the two branches.

### General syntax:
```walnut
?whenIsError(expr) { thenExpr } ~ { elseExpr }
```
or
```walnut
?whenIsError(expr) { thenExpr }
```

### Example:
```walnut
?whenIsError (x) { 1 }; /* no else branch means type 'x' as is */

?whenIsError (x) { 0 } ~ { x->length };
```

### How it works:
The `expr` is evaluated and checked if it is of type `Error`.
In case it is, then `thenExpr` is evaluated and this is the result.
Otherwise, the `elseExpr` is evaluated and used as the result. When there is no `elseExpr`, 
the value of `expr` is taken unmodified.

### Type refinement:
In case the `expr` is a variable, its type is automatically refined based:
- in `thenExpr` the type `Result<T, E>` is refined to `Error<R>`, `Any` is refined to `Error<Any>`
  and any other type is ignored.
- in `elseExpr` the type `Result<T, E>` is refined to `T`, and any other type remains the same.

In the example above if `x` is of type `Result<String, SomeError>`, then `x->length` is possible because 
  the compiler knows that `x` is of type `String`.


## ?noError
Check if an expression is of type `Error` and exits the function returning the error value.

### General syntax:
```walnut
?noError(expr)
```

### Example:
```walnut
?noError(x->sqrt);
```

### How it works:
The `expr` is evaluated and checked if it is of type `Error`.
In case it is, then it is directly returned as a result of the containing function.
Otherwise, the `expr` is used as the result.

### Type refinement:
In case the `expr` is of type `Result<T, E>` the resulting expression type is refined to `T`.
Additionally, the function return type should declare that it can return `Error<E>`. 

In the example above if `x` is of type `Real`, `x->sqrt` returns `Result<Real, NotANumber>` and there will
be an early return with the error value in case `x` is negative.

### Shorthand method call syntax:
While the default way to call a method is `object->method`, there is a shorthand syntax for calling a method and 
wrapping it in `?noError` - `object=>method` is a shorthand for `?noError(object->method)`.


## ?noExternalError
Check if an expression is of type `Error` with a value of type `ExternalError` and 
exits the function returning the error value.

### General syntax:
```walnut
?noExternalError(expr)
```

### Example:
```walnut
?noExternalError(dbQuery->execute);
```

### How it works:
The `expr` is evaluated and checked if it is of type `Error`. Additionally, the error value is checked if it is of type `ExternalError`.
In case it is, then it is directly returned as a result of the containing function.
Otherwise, the `expr` is used as the result.

### Type refinement:
In case the `expr` is of type `Result<T, E|ExternalError>` the resulting expression type is refined to `Result<T, E>`.
Additionally, the function return type should declare that it can return `Error<ExternalError>`. 

### Shorthand type syntax:
In general, the type `Result<T, ExternalError>` can be written as `Impure<T>` or even shorter - `*T`. 
This also works for result types containing other error types: `Result<T, E|ExternalError>` can be 
written as `Impure<Result<T, E>>` or `*Result<T, E>`.

### Shorthand method call syntax:
While the default way to call a method is `object->method`, there is a shorthand syntax for calling a method and
wrapping it in `?noExternalError` - `object|>method` is a shorthand for `?noExternalError(object->method)`.

