# Walnut Lang Language Reference
This document describes the language features of Walnut Lang (or in short Walnut).

- [Introduction](01-introduction.md)
- [Types and Values](02-types-and-values.md) 
- [Functions](03-functions.md)
- [Expressions](04-expressions.md)
- [Method Reference](05-method-reference.md)

## Example
```walnut
module fibonacci:

NonNegativeInteger = Integer<0..>;

fibonacciHelper = ^NonNegativeInteger => [NonNegativeInteger, NonNegativeInteger] ::
    ?whenTypeOf(#) is {
        type{Integer<1..>} : {
            r = fibonacciHelper(# - 1);
            [r.1, r.0 + r.1]
        },
        ~ : [1, 1]
    };

fibonacci = ^NonNegativeInteger => NonNegativeInteger :: fibonacciHelper(#).0;
main = ^Array<String> => String :: 0->upTo(10)->map(fibonacci)->printed;
/* returns: [1, 1, 2, 3, 5, 8, 13, 21, 34, 55, 89] */```
