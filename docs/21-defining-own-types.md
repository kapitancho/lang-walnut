# Defining custom types

## Overview

The Walnut language allows defining a wide range of custom types. 
Once defined and given a name, every custom type may be enriched with behavior.
This includes adding methods and defining casts from and to other types.
There are five different kinds of custom types in Walnut:
`Atoms`, `Enumerations`, `Aliases`, `Open` types, and `Sealed` types.

## Atoms

### Short syntax (examples)
```walnut
/* Atom type definition */
UnknownProduct = :[];

/* Atom value usage */
myProduct = UnknownProduct(); 
```
### Summary
The atoms represent values that form their own type.
One built-in atom type is `Null`, which has only one value, `null`.
Alongside with marking some specific values, 
methods and casts can be added to atom types.

### Usage examples
```walnut
/* Define behavior for the atom type */
UnknownProduct->getProductTitle(=> String) :: 'n/a';

/* Define a cast from the atom type to another type (Boolean) */
UnknownProduct ==> Boolean :: false;

myProduct = UnknownProduct();
title = myProduct->getProductTitle; /* 'n/a' */
?when(myProduct->asBoolean) 
      { 'The product is ok' } 
    ~ { 'The product is unknown' };
```
## Enumerations

### Short syntax (examples)
```walnut
/* Enumeration type definition */
Suit = :[Clubs, Diamonds, Hearts, Spades];

/* Enumeration value usage */
mySuit = Suit.Spades;
```

### Summary
Enumerations are types with a fixed set of values.
The built-in `Boolean` type is also an enumeration 
and its two values are `true` and `false`.

### Usage examples
```walnut
/* Define behavior for the enumeration type */
Suit->getSuitColor(=> String['black', 'red']) :: 
    ?whenTypeOf($) is {
        `Suit[Clubs, Spades]: 'black',
        `Suit[Diamonds, Hearts]: 'red' 
    };

/* Define a cast from the enumeration type to another type (Integer) */
Suit ==> Integer :: 
    ?whenValueOf($) is {
        Suit.Clubs: 1,
        Suit.Diamonds: 2,
        Suit.Hearts: 3,
        Suit.Spades: 4
    };

mySuit = Suit.Spades;
color = mySuit->getSuitColor; /* 'black' */
value = mySuit->asInteger; /* 4 */
```

## Aliases

### Short syntax (examples)
```walnut
MyPoint = [x: Real, y: Real];

Scalar = Real | String | Boolean | Null;
```

### Summary
By defining an alias, you can give a new name to an existing type.
This can be useful to make the code more readable but in addition,
methods and casts can be added to the alias type. 
As long as the compiler can infer the type of variable, 
these methods and casts can be used.

### Usage examples
```walnut
/* Define behavior for the alias type */
MyPoint->distanceTo(^MyPoint => Real) :: {{$x - #x} ** 2 + {$y - #y} ** 2}->sqrt;

/* Define a cast from the alias type to another type (String) */
MyPoint ==> String :: ['(x: ', $x->asString, ', y:', $y->asString, ')']
    ->combineAsString('');

p = [x: 3.3, y: 1];
q = [x: 7.1, y: -2];
distance = p->distanceTo(q); /* 5 */
s = p->asString; /* '(x: 3.3, y: 1.0)' */
```

## Open types

### Short syntax (examples)
```walnut
GpsPoint = #[latitude: Real<-90..90>, longitude: Real<-180..180>]

OddInteger #Integer @ NotAnOddInteger :: 
    ?when({# % 2} == 0) { => @NotAnOddInteger[] }
```

### Summary
Open types are types for which the value that they are bound to is exposed and fully accessible.
Their value can be based on any type, and they are treated as distinct values so MyInteger(5) is different from 5.
More on open type constructors and invariant checkers can be found 
[here](#Constructors and invariant validators).

### Usage examples
```walnut
square = ^x: Integer => Integer :: x ** 2;
addOne = ^x: OddInteger => Integer :: x->value + 1;
isPointNorth = ^pt: GpsPoint => Boolean :: pt.latitude > 0;

x = ?noError(OddInteger(5));
xSquared = square(5); /* 25 */
xPlusOne = addOne(x); /* Compilation error - OddInteger expected but Integer provided */
xIsNorth = isPointNorth[latitude: 51.5074, longitude: 0.1278]; /* true */
```

## Sealed types

### Short syntax (examples)
```walnut
Product = $[id: Integer<1..>, name: String<1..100>, price: Mutable<Real<0..>>];

Point = $[x: Real, y: Real];
```

### Summary
Sealed types are used to encapsulate the data and behavior similarly to OOP.
The only way to access the properties is by using methods defined on the sealed type.
More on sealed type constructors and invariant checkers can be found
[here](#Constructors and invariant validators).


### Usage examples
```walnut
Product->updatePrice(^Real) :: $price->SET(#);
Product->name(=> String) :: $name;

myProduct = Product[1, 'Bread', 1.5];
myProduct->updatePrice(1.6);
myProductName = myProduct->name; /* 'Bread' */

myPointFn = ^[x: Real, y: Real] :: ... /* some function */

myPoint = Point[3.3, 1];
myPointFn(myPoint); /* Compilation error - the base type matches but the class is sealed */
```

## Constructors and invariant validators
Both subtypes and sealed types may have constructors and invariant validators. 
The constructors are used to create new values of the type, in case the initial input data
is different compared to their base type definition. 
The invariant validators are used to ensure that the values of the type are always valid.
Based on the needs, every type may have a constructor, an invariant validator, both or none.

### Constructors


```walnut```

While 

The following syntax can be used to define a constructor for a subtype or a sealed type:
```
Article(String) %% [~Clock] :: [title: #, publishDate: %.clock->now];
```
Once again this is presented as *Constructor->Article(String) %% [~Clock] :: [title: #, publishDate: %.clock->now];*.

### Invariant validators
This is a unique feature of Walnut. There is a way to provide a validator function which in case it explicitly returns an
error the value construction is rejected and the error is returned instead. It may again be used for both subtypes and sealed types.
Invariant validators and constructors can be used or omitted independently.
```walnut
NotAnOddInteger = :[]; /* define an Atom type for the error */
OddInteger = #Integer @ NotAnOddInteger :: ?whenValueOf(# % 2) is { 0: => Error(NotAnOddInteger[]) };
```
It is important to know that while the constructor can be bypassed by using the `JsonValue->hydrateAs` method,
the invariant validator is always executed. In case the validator returns an error, the `hydrateAs` method will return a `HydrationError`.
