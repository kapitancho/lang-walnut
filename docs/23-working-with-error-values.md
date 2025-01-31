# Working with Error Values
Error values are a special type of values in Walnut. 
They can be used to signal that something went wrong during the execution of the program, but 
they can as well be used to reflect some domain-specific error conditions.

The language does not have exceptions, so the error values are used to propagate the error information.
The most convenient way to provide exception-like behavior is to use the `?noError` and `?noExternalError` 
conditionals.

## Creating Error Values
Any base value can be used to create an error value. The error value is created by using the `@` operator.

### General syntax:
```walnut
@expr
```
or
```walnut
Error(expr)
```

### Examples:
```walnut
@'File not found'
Error(UnknownProduct[])
```

## ExternalError
ExternalError is a special type of error value that can be used to signal that the error was caused by 
an external system or that the error is not relevant to the current context.

### Converting to ExternalError
Any error value can be converted to ExternalError by using the `errorAsException` method.

#### General syntax:
```walnut
expr->errorAsException(errorMessageString)
```
or
```walnut
expr *> (errorMessageString) /* which is a shorthand for 
?noExternalError(expr->errorAsException(errorMessageString)) */
```

#### Examples:
```walnut
value->errorAsException('Error message')
value *> ('Error message')
```

#### How it works:
The `expr` is evaluated and checked if it is of type `Error`. If it is, 
then an error value of type `ExternalError` is returned based on the 
original error and the error message. If `expr` is not of type `Error`, 
then the `expr` is used as the result.

### Detailed Example:
```walnut
executeGetProductByIdQuery = 
    ^[productId: Integer] => Result<Array<Row>, DatabaseError> :: ... /* some code */

getProductById = ^[productId: Integer] => Impure<Result<Product, ProductNotFound> :: {
    /* Early return in case of a DatabaseError */
    rows = executeGetProductByIdQuery(productId) *> ('Product not found');
    /* at this point `rows` is of type Array<Row> */
    ?whenTypeOf(rows) is {
        type{Array<Row, 1..>}: convertRowToProduct(rows.0),
        ~: Error(ProductNotFound[])
    }
}
```