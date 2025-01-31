# Expressions

Everything in Walnut is an expression. 
They are grouped into the following categories: values, variable assignments, early returns, conditionals, 
function calls, and sequences.

## Values

Values are the simplest form of expressions. They can be constants or references to variables.

### Constants
```walnut
3.14
null
type{Real}
^Integer => String
MyAtom[]
Suit.Clubs
'Hello'
42
[1, -2.61] /* A Tuple expression */ 
[a: 1, b: -2.61] /* A Record expression */
```
### Variables
```walnut
x
```

## Variable Assignments
```walnut
x = 42;
y = [a: 3, b: ['Hello', 'World'], c: type{String}];
```

## Early Returns
There are several ways to immediately return a value from a function.

Unconditional return:
```walnut
=> 42;
```

Return in case of any error value:
```walnut
x = @'File not found';
?noError(x);
```    

Return in case of an error value of type `ExternalError`:
```walnut
x = @ExternalError[ ... ];
?noExternalError(x);
```    

## Conditionals
More about conditionals can be read [here](22-conditional-expressions.md)
There are four conditionals with a similar syntax:

### ?whenValueOf
```walnut
?whenValueOf(x) is { 42: 1, 1000: 2, ~: 0 }; /* ~ is the default branch */
```

### ?whenTypeOf
```walnut
?whenTypeOf(x) is {
    type{Integer<1..>} : 1,
    type{Real} : 2,
    ~ : 0 /* ~ is the default branch */
};
```

### ?whenIsTrue
```walnut
?whenIsTrue { x > 0: 1, x < 0: -1, ~: 0 }; /* ~ is the default branch */
```

### ?when
```walnut
?when (x > 0) { 1 }; /* no else branch means 'null' */

?when (x > 0) { 1 } ~ { -1 };
```

### ?whenIsError
```walnut
?whenIsError (x) { 1 }; /* no else branch means 'null' */

?whenIsError (x) { 1 } ~ { -1 };
```

Warning: in all three cases if the default branch (~) is omitted the expression value for the branch will be null unless the
analyser can detect that all cases are covered.


## Function Calls
More about functions can be read [here](03-functions.md) 

### Simple functions
```walnut
myFunction(42); /* call with a single argument */
myDistance[from: 'London', to: 'Paris']; /* call with a record argument */
getProducts(); /* call with no arguments - in reality this is getProducts(null) */
```

### Behavior-driven functions (methods)
```walnut
myObject->myMethod(42); /* call with a single argument */
myObject->myMethod[argument: 42]; /* call with a record argument */
myObject->myMethod; /* call with no arguments - in reality this is myObject->myMethod(null) */
```

In addition to the above syntax, there is a special syntax for early returns:
```walnut
myObject => myMethod(42); /* same as ?noError(myObject->myMethod(42)) */
myObject |> myMethod(42); /* same as ?noExternalError(myObject->myMethod(42)) */
```
Another related syntax sugar is:
```walnut
someValue *> ('Unexpected error'); /* same as this call someValue->errorAsExternal('Unexpected error') */ 
```
which converts any error into an `ExternalError`. 


## Sequences
The sequences are a series of expressions separated by semicolons. 
The value of the sequence is the value of the last expression in the sequence.

```walnut
{
    x = 4;
    y = 3;
    x + y
}
```