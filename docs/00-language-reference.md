# Walnut Lang Language Reference
This document describes the features of the Walnut Programming Language (or in short Walnut).

- [Introduction](01-introduction.md)
- [Types and Values](02-types-and-values.md) 
- [Functions](03-functions.md)
- [Expressions](04-expressions.md)
- [Method Reference](05-method-reference.md)

## Example
```walnut
module fibonacci:

NonNegativeInteger = Integer<0..>;

fibonacciHelper = ^num: NonNegativeInteger => [NonNegativeInteger, NonNegativeInteger] ::
    ?whenTypeOf(num) is {
        type{Integer<1..>} : {
            r = fibonacciHelper(num - 1);
            [r.1, r.0 + r.1]
        },
        ~ : [1, 1]
    };

fibonacci = ^num: NonNegativeInteger => NonNegativeInteger :: fibonacciHelper(num).0;
main = ^Array<String> => String :: 0->upTo(10)->map(fibonacci)->printed;
/* returns: [1, 1, 2, 3, 5, 8, 13, 21, 34, 55, 89] */```
