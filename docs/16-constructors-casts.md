# Constructors, Validators, and Cast Functions

## Overview

Walnut provides a powerful system for defining how types can be created, validated, and converted. This chapter covers three related mechanisms:

- **Constructors** - Convert from one type to another during type creation
- **Invariant Validators** - Ensure values satisfy type constraints (always executed)
- **Cast Functions** - Convert between types after creation

These mechanisms work together to provide type-safe conversions with comprehensive error handling.

## Constructors

### Basic Syntax

Constructors define how to create values of a type.

**Syntax:** `TypeName(ParamType) :: constructorBody`

**With dependencies:** `TypeName(ParamType) %% DepType :: constructorBody`

**With error handling:** `TypeName(ParamType) @ ErrorType :: constructorBody`

**Full form:** `TypeName(ParamType) @ ErrorType %% DepType :: constructorBody`

[marian] If paramType is a record or tuple, it can be written directly as parameters (without wrapping in parentheses):
```walnut
TypeName[a: Integer, b: Integer] :: constructorBody
TypeName[Integer, Integer] :: constructorBody
```

### Syntactic Sugar

Constructors are syntactic sugar for defining a method on the `Constructor` atom:

```walnut
/* This constructor definition: */
TypeName(ParamType) :: constructorBody

/* Is syntactic sugar for: */
Constructor->asTypeName(ParamType => TypeName) :: constructorBody
```

With error handling:

```walnut
/* This constructor definition: */
TypeName(ParamType) @ ErrorType :: constructorBody

/* Is syntactic sugar for: */
Constructor->asTypeName(ParamType => Result<TypeName, ErrorType>) :: constructorBody
```

### Simple Constructor Examples

```walnut
/* From demo-constructor.nut */
/* Sealed type without validator */
D := $[x: Integer, y: Integer];

/* Constructor that converts Integers */
D[t: Integer, u: Integer] @ E :: {
    z = #u - #t;
    ?whenTypeOf(z) is { `Integer<1..>: null, ~: => @E };
    [x: #t, y: #u]
};

/* Usage */
d1 = D[3, 8]; /* OK: 8 - 3 = 5 (>= 1) */
d2 = D[7, 4]; /* Error: returns @E */
```

### Constructor with Type Conversion

Constructors can convert from different parameter types:

```walnut
/* From demo-all.nut */
MySealed := $[a: Integer, b: Integer];

/* Constructor converts Real to Integer */
MySealed[a: Real, b: Real] :: [a: #a->asInteger, b: #b->asInteger];

/* Usage */
obj = MySealed[3.7, -2.3]; /* Result: MySealed[a: 3, b: -2] */
```

### Constructor with Dependencies

Constructors can use dependency injection:

```walnut
/* From demo-all.nut */
MyAtom := ();
MySealed := $[a: Integer, b: Integer];

/* Constructor with dependency */
MySealed[a: Real, b: Real] %% MyAtom :: [a: #a->asInteger, b: #b->asInteger];
```

### Constructor for No-Argument Creation

Constructors can have no parameters:

```walnut
/* From shopping-cart/model.nut */
ShoppingCart := $[items: Mutable<Map<ShoppingCartItem>>];

/* No-argument constructor */
ShoppingCart() :: [items: mutable{Map<ShoppingCartItem>, [:]}];

/* Usage */
cart = ShoppingCart();
```

### Constructor Error Handling

Constructors can return errors when conversion fails:

```walnut
/* From demo-constructor.nut */
R := ();
P := #[x: Integer, y: Integer] @ Q :: {
    z = #y - #x;
    ?whenTypeOf(z) is { `Integer<10..>: null, ~: => @Q }
};

/* Constructor with validation */
P[t: String, u: Integer] @ R :: {
    z = #u - #t->length;
    ?whenTypeOf(z) is { `Integer<1..>: null, ~: => @R };
    [x: #t->length, y: #u]
};

/* Usage */
p1 = P[t: 'hello', u: 17]; /* OK: 17 - 5 = 12 */
p2 = P[t: 'hello', u: 3];  /* Error: returns @R (3 - 5 = -2 < 1) */
```

### Accessing Constructor Parameters

Within a constructor body, you can always use `#` to access the parameters but there are also other options:

```walnut
/* Single parameter (no name specified) */
TypeName(Integer) :: [field: #];

/* Single parameter (name specified) */
TypeName(i: Integer) :: [field: i];

/* Single parameter (name is the same as field but lowercase) */
TypeName(~Product) :: [field: product];

/* Direct tuple-style parameters */
TypeName[Integer, Integer] :: [x: #0, y: #1];

/* Direct record-style parameters */
TypeName[a: Integer, b: Integer] :: [x: #a, y: #b];
```

### Constructor vs Direct Creation

```walnut
/* Sealed type */
MySealed := $[a: Integer, b: Integer];

/* Direct creation (no constructor) */
obj1 = MySealed[a: 3, b: -2];

/* With constructor defined */
MySealed[x: Real, y: Real] :: [a: #x->asInteger, b: #y->asInteger];

/* Now can use constructor */
obj2 = MySealed[x: 3.7, y: -2.3];
```

### Multiple Constructors

Types cannot have multiple constructors

## Invariant Validators

### Basic Syntax

Validators ensure that values satisfy type invariants. Unlike constructors, validators **always execute**, even during hydration.

**Syntax:** `TypeName := #UnderlyingType @ ErrorType :: validationBody`

**For sealed types:** `TypeName := $UnderlyingType @ ErrorType :: validationBody`

### Validator Execution

Validators are **always** executed:
- When creating values via constructors
- When hydrating values with `hydrateAs`
- When values are created by any means

This ensures type invariants are never violated.

### Simple Validator Example

```walnut
/* From cast11.nut */
NotAnOddInteger := ();

OddInteger := #Integer @ NotAnOddInteger :: {
    ?whenValueOf(# % 2) is {
        1: #,
        ~: @NotAnOddInteger
    }
};

/* Usage */
odd1 = OddInteger(5);  /* OK */
odd2 = OddInteger(4);  /* Error: returns @NotAnOddInteger */
```

### Validator for Record Types

```walnut
/* From cast11.nut */
InvalidRange := ();

Range := #[from: Integer, to: Integer] @ InvalidRange :: {
    ?whenIsTrue {
        #from < #to: #,
        ~: @InvalidRange
    }
};

/* Usage */
r1 = Range([14, 23]); /* OK: 14 < 23 */
r2 = Range([23, 14]); /* Error: returns @InvalidRange */
```

### Validator for Tuple Types

```walnut
/* From cast11.nut */
InvalidRange := ();

Range2 := #[Integer, Integer] @ InvalidRange :: {
    ?whenIsTrue {
        #.0 < #.1: #,
        ~: @InvalidRange
    }
};

/* Usage */
r = Range2([14, 23]); /* OK: 14 < 23 */
```

### Complex Validation Logic

```walnut
/* From core.nut */
InvalidRange := ();

IntegerRange := #[minValue: Integer|MinusInfinity, maxValue: Integer|PlusInfinity] @ InvalidRange ::
    ?whenTypeOf(#) is {
        `[minValue: Integer, maxValue: Integer]:
            ?when (#minValue > #maxValue) { => @InvalidRange }
    };
```

### Returning Validation Errors

Use the `=> @ErrorType` syntax to return errors:

```walnut
/* From demo-bug.nut */
InvalidPoint := ();

Point := #[x: Real, y: Real] @ InvalidPoint ::
    ?whenValueOf([#x, #y]) is {
        [0, 0]: => @InvalidPoint
    };

/* Origin point is invalid */
p1 = Point([3, 4]);  /* OK */
p2 = Point([0, 0]);  /* Error: returns @InvalidPoint */
```

### Validator Terminology

When validation fails, use `=> @ErrorType` for early return:

```walnut
TypeName := #BaseType @ ErrorType :: {
    /* Validation logic */
    ?when(invalidCondition) {
        => @ErrorType  /* Early return with error */
    };

    /* If we reach here, validation passed */
    #  /* Return the validated value */
};
```

### Validators Always Run

Unlike constructors, validators **cannot be bypassed**:

```walnut
/* From demo-constructor.nut */
D := $[x: Integer, y: Integer];
D[t: Integer, u: Integer] @ E :: {
    z = #u - #t;
    ?whenTypeOf(z) is { `Integer<1..>: null, ~: => @E };
    [x: #t, y: #u]
};

/* Using constructor */
d1 = D[3, 8];  /* Constructor runs */

/* Using hydration (bypasses constructor) */
d2 = [x: 3, y: 8]->hydrateAs(`D);  /* Constructor skipped */

/* If D had a validator, it would run in both cases */
```

## Constructor and Validator Together

Types can have both constructors and validators. The constructor runs first (during construction), then the validator always runs:

```walnut
/* From demo-constructor.nut */
J := ();
K := ();

/* Validator checks y - x >= 1 */
L := $[x: Integer, y: Integer] @ K :: {
    z = #y - #x;
    ?whenTypeOf(z) is { `Integer<1..>: null, ~: => @K }
};

/* Constructor checks u >= 1 */
L[t: Integer, u: Integer] @ J :: {
    ?whenTypeOf(#u) is { `Integer<1..>: null, ~: => @J };
    [x: #t, y: #u]
};

/* Examples */
L[3, 8];   /* OK: constructor and validator pass */
L[-4, -7]; /* Error @K: validator fails (−7 − (−4) = −3 < 1) */
L[-7, -4]; /* Error @K: validator fails (−4 − (−7) = 3, but u = -4 < 1 in constructor) */
```

### Execution Order

1. **Constructor** runs (if using constructor syntax)
2. **Validator** always runs (if defined)
3. If either fails, an error is returned

### Multiple Error Types

Constructor and validator can have different error types:

```walnut
/* From demo-constructor.nut */
Q := ();  /* Validator error */
R := ();  /* Constructor error */

P := #[x: Integer, y: Integer] @ Q :: {
    z = #y - #x;
    ?whenTypeOf(z) is { `Integer<10..>: null, ~: => @Q }
};

P[t: String, u: Integer] @ R :: {
    z = #u - #t->length;
    ?whenTypeOf(z) is { `Integer<1..>: null, ~: => @R };
    [x: #t->length, y: #u]
};

/* Error from constructor */
p1 = P[t: 'hello', u: 3];  /* Returns @R */

/* Error from validator */
p2 = P[t: 'hello', u: 10]; /* Returns @Q */

/* Success */
p3 = P[t: 'hello', u: 17]; /* OK */
```

## Cast Functions

### Basic Syntax

Cast functions define conversions between types. They are called using the `->asTargetType` method syntax or using the `as` method on `Any`.

**Syntax:** `FromType ==> ToType :: body`

**With dependencies:** `FromType ==> ToType %% DepType :: body`

**With error handling:** `FromType ==> ToType @ ErrorType :: body`

**Full form:** `FromType ==> ToType @ ErrorType %% DepType :: body`

### Syntactic Sugar

Cast functions are syntactic sugar for defining a method:

```walnut
/* This cast definition: */
FromType ==> ToType :: body

/* Is syntactic sugar for: */
FromType->asToType(Null => ToType) :: body
```

With error handling:

```walnut
/* This cast definition: */
FromType ==> ToType @ ErrorType :: body

/* Is syntactic sugar for: */
FromType->asToType(Null => Result<ToType, ErrorType>) :: body
```

### Simple Cast Examples

```walnut
/* From demo-asstring.nut */
Message := $[text: String];

/* Cast from Message to String */
Message ==> String :: $text;

/* Usage */
msg = Message[text: 'hello'];
str = msg->asString; /* 'hello' */

/* or using Any->as */
str2 = msg->as(`String); /* Ok('hello') */
```

### Cast from Open Type

```walnut
/* From demo-asstring.nut */
Greeting := $[text: String];

/* Cast from Greeting to String */
Greeting ==> String :: $text;

/* Usage */
greeting = Greeting[text: 'Hello, World!'];
text = greeting->asString; /* 'Hello, World!' */
```

### Cast with Computation

```walnut
/* From demo-asinteger.nut */
Message := #[text: String];

/* Cast to Integer (length of text) */
Message ==> Integer :: $text->length;

/* Usage */
msg = Message[text: 'hello'];
len = msg->asInteger; /* 5 */
```

### Error-Free Casts

When conversion cannot fail, omit the error type:

```walnut
/* From shopping-cart/model.nut */
ProductId := String<1..>;

/* Simple unwrapping cast */
ProductId ==> String :: $$;

/* Usage */
id = ProductId!'product-123';
str = id->asString; /* 'product-123' */
```

### Casts with Error Handling

When conversion can fail, specify the error type:

```walnut
/* From demo-asinteger.nut */
/* Built-in error type from core.nut */
NotANumber := ();

/* Cast from String to Integer (parsing) */
fromString = ^String => Result<Integer, NotANumber> :: #->asInteger;

/* Usage */
str1 = '42';
i1 = str1->asInteger; /* Ok(42) */

str2 = 'hello';
i2 = str2->asInteger; /* Error(NotANumber) */
```

### Complex Cast Examples

```walnut
/* From shopping-cart/model.nut */
Shirt := $[id: ProductId, model: ProductTitle, price: ProductPrice, size: ShirtSize, color: ShirtColor];

/* Cast to shopping cart product */
Shirt ==> ShoppingCartProduct :: [
    id: $id,
    title: $model->asString + ' ' + {$color->asString} + ' ' + {$size->asString},
    price: $price
];

/* Usage */
shirt = Shirt[
    id: ProductId!'shirt-1',
    model: ProductTitle!'T-Shirt',
    price: ProductPrice!19.99,
    size: ShirtSize.M,
    color: ShirtColor.Blue
];
cartProduct = shirt->asShoppingCartProduct;
```

### Multiple Casts from Same Type

Types can define multiple casts to different target types:

```walnut
/* From shopping-cart/model.nut */
Monitor := $[id: ProductId, brand: BrandName, model: ProductTitle, price: ProductPrice, resolution: MonitorResolution, sizeInInches: MonitorSize];

/* Cast to ShoppingCartProduct */
Monitor ==> ShoppingCartProduct :: [
    id: $id,
    title: {$brand->asString} + ' ' + {$model->asString} + ' ' + {$resolution->asString} + ' ' + $sizeInInches->value->asString + '"',
    price: $price
];

/* Could also define cast to String */
/* Monitor ==> String :: $model->asString; */
```

## Built-in Casts

### The asString Cast

Most types can be converted to String:

```walnut
/* From demo-asstring.nut */

/* Boolean to String */
fromBoolean = ^Boolean => String['true', 'false'] :: #->asString;
fromBoolean(true);  /* 'true' */
fromBoolean(false); /* 'false' */

/* Integer to String */
fromInteger = ^Integer => String :: #->asInteger;
fromInteger(42);    /* '42' */
fromInteger(-17);   /* '-17' */

/* Real to String */
fromReal = ^Real => String :: #->asString;
fromReal(3.14);     /* '3.14' */

/* Null to String */
fromNull = ^Null => String['null'] :: #->asString;
fromNull(null);     /* 'null' */

/* Enumeration to String */
Suit := (Spades, Hearts, Diamonds, Clubs);
fromEnum = ^Suit => String['Spades', 'Hearts', 'Diamonds', 'Clubs'] :: #->asString;
fromEnum(Suit.Hearts); /* 'Hearts' */

/* Type to String */
fromType = ^Type => String :: #->asString;
fromType(`Integer); /* Type name as string */
```

### The asInteger Cast

```walnut
/* From demo-asinteger.nut */

/* Boolean to Integer */
fromBoolean = ^Boolean => Integer[0, 1] :: #->asInteger;
fromBoolean(true);   /* 1 */
fromBoolean(false);  /* 0 */

/* Real to Integer (truncation) */
fromReal = ^Real => Integer :: #->asInteger;
fromReal(3.14);      /* 3 */
fromReal(-2.7);      /* -2 */

/* String to Integer (parsing, can fail) */
fromString = ^String => Result<Integer, NotANumber> :: #->asInteger;
fromString('42');    /* Ok(42) */
fromString('hello'); /* Error(NotANumber) */

/* Mutable to Integer */
fromMutableInteger = ^Mutable<Integer<5..20>> => Integer<5..20> :: #->asInteger;
```

### The asReal Cast

```walnut
/* From demo-asreal.nut */

/* Boolean to Real */
fromBoolean = ^Boolean => Real[0, 1] :: #->asReal;
fromBoolean(true);   /* 1.0 */
fromBoolean(false);  /* 0.0 */

/* Integer to Real */
fromInteger = ^Integer => Real :: #->asReal;
fromInteger(42);     /* 42.0 */

/* String to Real (parsing, can fail) */
fromString = ^String => Result<Real, NotANumber> :: #->asReal;
fromString('3.14');  /* Ok(3.14) */
fromString('hello'); /* Error(NotANumber) */

/* Mutable to Real */
fromMutableReal = ^Mutable<Real<5..20>> => Real<5..20> :: #->asReal;
```

### The asBoolean Cast

```walnut
/* From demo-asboolean.nut */

/* Integer to Boolean (zero = false, non-zero = true) */
fromInteger = ^Integer => Boolean :: #->asBoolean;
fromInteger(0);    /* false */
fromInteger(42);   /* true */
fromInteger(-5);   /* true */

/* Real to Boolean */
fromReal = ^Real => Boolean :: #->asBoolean;
fromReal(0.0);     /* false */
fromReal(3.14);    /* true */

/* String to Boolean (non-empty = true) */
fromString = ^String => Boolean :: #->asBoolean;
fromString('');        /* false */
fromString('hello');   /* true */

/* Null to Boolean */
fromNull = ^Null => False :: #->asBoolean;
fromNull(null);    /* false */
```

### The asJsonValue Cast

The `asJsonValue` method converts values to JSON-compatible types:

```walnut
/* From demo-money.nut */
Money := #[~Currency, ~Amount];

/* Can be converted to JSON */
myMoney = Money[currency: Currency.EUR, amount: Amount!42.5];
json = myMoney->asJsonValue;
```

## Dependency Provider Casts

### Basic Syntax

Dependency provider casts create values from the dependency container:

**Syntax:** `==> TypeName %% DepType :: body`

This is shorthand for:

**Full form:** `DependencyContainer ==> TypeName %% DepType :: body`

### Simple Example

```walnut
/* From cast0.nut */
B := #[a: Integer];

/* Provide default instance of B */
==> B :: B[3];

/* Usage in dependency injection */
/* When B is needed, the default instance will be used */
```

### With Dependencies

```walnut
/* From cast0.nut */
A = ^String => Integer;
B := #[a: Integer];
B ==> A :: ^v: String => Integer :: v->length + $a;

/* B depends on itself (gets the default instance) */
==> A %% B;

/* When A is needed, B will be resolved first */
```

### Complex Example

```walnut
/* From demo-money.nut */
ExchangeRateProvider := ^[from: Currency, to: Currency] => Real;

==> MoneyCurrencyConvertor %% [~ExchangeRateProvider] ::
    ^[money: Money, toCurrency: Currency] => *Money :: {
        ?when(money.currency == toCurrency) { money };

        rate = %exchangeRateProvider([from: money.currency, to: toCurrency]);
        Money[currency: toCurrency, amount: Amount!{money.amount->value * rate}]
    };
```

### Nested Dependencies

```walnut
/* From demo-book2.nut */
==> BookByIsbn %% [~Library] ::
    ^Isbn => Result<Book, UnknownBook> :: {
        book = %library.books->value->item(#->asString);
        ?whenTypeOf(book) is {
            `Book: book,
            ~: @UnknownBook[#]
        }
    };
```

## The Any->as(Type<T>) Method

### Dynamic Casting

The `as` method performs dynamic casting at runtime:

**Syntax:** `value->as(`TargetType)`

**Result:** `Result<TargetType, CastNotAvailable>`

### Basic Example

```walnut
/* From cast14.nut */
sq = Square[sideLength: 5.0];

/* Cast to interface type */
figure = sq->as(`Figure);

/* Use the cast value */
area = figure.area(); /* Calls method on Figure */
```

### Cast with Type Variables

```walnut
/* From cast14.nut */
s = `Figure;

/* Cast using type variable */
ci = Circle[radius: 3.0];
figure = ci->as(s);
```

### Error Handling

The `as` method returns a Result type:

```walnut
/* From demo-asstring.nut */
fromAny = ^Any => Result<String, CastNotAvailable> :: #->asString;

/* Success case */
result1 = fromAny(42);  /* Ok('42') */

/* Failure case */
result2 = fromAny([]); /* Error(CastNotAvailable[from: `Array, to: `String]) */
```

### Using Cast Results

```walnut
/* From cast16.nut */
a = getSomeFruit();

/* Cast and use result */
appleText = a->as(`Apple).asText();

/* The cast returns Result<Apple, CastNotAvailable> */
/* If successful, can call methods on Apple */
```

### Type Inference

The cast result type is inferred from the target type:

```walnut
/* Cast to specific type */
ProductId := #Integer<1..>;

value = 12;
result = value->as(`ProductId);  /* Result<ProductId, CastNotAvailable> */
```

## Constructor vs Hydration

### Key Differences

- **Constructors** are user-defined conversion functions
- **Hydration** bypasses constructors and directly creates values
- **Validators** always run, even during hydration

### Constructor Behavior

```walnut
/* From demo-constructor.nut */
D := $[x: Integer, y: Integer];

D[t: Integer, u: Integer] @ E :: {
    z = #u - #t;
    ?whenTypeOf(z) is { `Integer<1..>: null, ~: => @E };
    [x: #t, y: #u]
};

/* Using constructor */
d1 = D[3, 8];  /* Constructor runs: validates 8 - 3 = 5 >= 1 */

/* Using hydration */
d2 = [x: 3, y: 8]->hydrateAs(`D);  /* Constructor SKIPPED */
```

### Validator Always Runs

```walnut
/* Type with validator */
H := $[x: Integer, y: Integer] @ G :: {
    z = #y - #x;
    ?whenTypeOf(z) is { `Integer<1..>: null, ~: => @G }
};

/* Using constructor */
h1 = H[3, 8];  /* Validator runs */

/* Using hydration */
h2 = [x: 3, y: 8]->hydrateAs(`H);  /* Validator STILL runs */
```

### When to Use Each

**Use constructors** when:
- Converting from different input types
- Need conversion logic (e.g., Real to Integer)
- Want validation specific to construction

**Use hydration** when:
- Deserializing from external sources (JSON, database)
- Need to bypass conversion logic
- Working with raw data structures

**Use validators** when:
- Enforcing type invariants
- Validation must always apply
- Ensuring data integrity from any source

## Practical Examples

### Example: Product Constructor

```walnut
/* Define types */
ProductId := String<1..>;
ProductTitle := String<1..>;
ProductPrice := Real<0..>;

InvalidProduct := ();

Product := $[id: ProductId, title: ProductTitle, price: ProductPrice];

/* Constructor from raw values */
Product[id: String, title: String, price: Real] @ InvalidProduct :: {
    ?when(id->length < 1) { => @InvalidProduct };
    ?when(title->length < 1) { => @InvalidProduct };
    ?when(price < 0) { => @InvalidProduct };

    [
        id: ProductId!id,
        title: ProductTitle!title,
        price: ProductPrice!price
    ]
};

/* Usage */
p1 = Product[id: 'prod-1', title: 'Widget', price: 9.99]; /* OK */
p2 = Product[id: '', title: 'Widget', price: 9.99];       /* Error */
```

### Example: OddInteger Validator

```walnut
/* From cast11.nut */
NotAnOddInteger := ();

OddInteger := #Integer @ NotAnOddInteger :: {
    ?whenValueOf(# % 2) is {
        1: #,      /* Return value if odd */
        ~: @NotAnOddInteger  /* Return error if even */
    }
};

/* Usage in function */
oddOnly = ^Integer => Result<Integer, NotAnOddInteger> :: {
    ?whenValueOf(# % 2) is {
        1: #,
        ~: @NotAnOddInteger
    }
};

[
    oddOnly(5),        /* OK: 5 */
    OddInteger(5)      /* OK: OddInteger(5) */
];
```

### Example: Shopping Cart with Casts

```walnut
/* From shopping-cart/model.nut */

/* Base product info for shopping cart */
ShoppingCartProduct = [id: ProductId, title: String, price: ProductPrice];

/* Different product types */
Shirt := $[id: ProductId, model: ProductTitle, price: ProductPrice, size: ShirtSize, color: ShirtColor];
Monitor := $[id: ProductId, brand: BrandName, model: ProductTitle, price: ProductPrice, resolution: MonitorResolution, sizeInInches: MonitorSize];
Wine := $[id: ProductId, producer: WineProducer, name: ProductTitle, price: ProductPrice, year: WineProductionYear];

/* Cast Shirt to ShoppingCartProduct */
Shirt ==> ShoppingCartProduct :: [
    id: $id,
    title: $model->asString + ' ' + {$color->asString} + ' ' + {$size->asString},
    price: $price
];

/* Cast Monitor to ShoppingCartProduct */
Monitor ==> ShoppingCartProduct :: [
    id: $id,
    title: {$brand->asString} + ' ' + {$model->asString} + ' ' + {$resolution->asString} + ' ' + $sizeInInches->value->asString + '"',
    price: $price
];

/* Cast Wine to ShoppingCartProduct */
Wine ==> ShoppingCartProduct :: [
    id: $id,
    title: {$producer->asString} + ' ' + $name->asString + ' ' + {$year->value->asString},
    price: $price
];

/* Usage */
shirt = Shirt[
    id: ProductId!'s1',
    model: ProductTitle!'T-Shirt',
    price: ProductPrice!19.99,
    size: ShirtSize.L,
    color: ShirtColor.Blue
];

monitor = Monitor[
    id: ProductId!'m1',
    brand: BrandName!'Samsung',
    model: ProductTitle!'Odyssey',
    price: ProductPrice!599.99,
    resolution: MonitorResolution.UHD,
    sizeInInches: MonitorSize!27.0
];

/* Convert to common cart format */
shirtInCart = shirt->asShoppingCartProduct;
/* Result: [id: ProductId!'s1', title: 'T-Shirt Blue L', price: 19.99] */

monitorInCart = monitor->asShoppingCartProduct;
/* Result: [id: ProductId!'m1', title: 'Samsung Odyssey UHD 27"', price: 599.99] */
```

### Example: Range Validator

```walnut
/* From cast21.nut */
InvalidIntegerRange := ();

MyIntegerRange := #[from: Integer, to: Integer] @ InvalidIntegerRange :: {
    ?when(#from >= #to) { => @InvalidIntegerRange };
    #  /* Return validated value */
};

/* Usage */
range1 = MyIntegerRange([from: 1, to: 10]);  /* OK */
range2 = MyIntegerRange([from: 10, to: 1]);  /* Error: InvalidIntegerRange */
```

## Error Handling Patterns

### Result Types

All fallible casts and constructors return Result types:

```walnut
/* Success */
Result<Value, Error> :: Ok(value)

/* Failure */
Result<Value, Error> :: Error(errorValue)
```

### Multiple Error Types

Use union types for multiple possible errors:

```walnut
/* From demo-constructor.nut */
Q := ();
R := ();

P := #[x: Integer, y: Integer] @ Q :: {
    z = #y - #x;
    ?whenTypeOf(z) is { `Integer<10..>: null, ~: => @Q }
};

P[t: String, u: Integer] @ R :: {
    z = #u - #t->length;
    ?whenTypeOf(z) is { `Integer<1..>: null, ~: => @R };
    [x: #t->length, y: #u]
};

/* Function that uses P constructor */
pq = ^Null => Result<P, Q|R> :: P[t: 'hello', u: 7];
/* Return type is Result<P, Q|R> - can fail with Q or R */
```

### Handling Cast Errors

```walnut
/* From core.nut */
CastNotAvailable := [from: Type, to: Type];

/* Using Any->as with error handling */
value = getSomeValue();  /* Type: Any */
result = value->as(`String);

?whenTypeOf(result) is {
    `Ok<String>: {
        /* Use result.value */
    },
    `Error<CastNotAvailable>: {
        /* Handle error: result.value.from, result.value.to */
    }
};
```

## Best Practices

### When to Use Constructors

Use constructors when:
- Converting between different representations
- Need type-specific conversion logic
- Want to control how external data enters the type system
- Conversion is optional (can be bypassed with hydration)

### When to Use Validators

Use validators when:
- Enforcing type invariants that must **always** hold
- Validation must occur even during deserialization
- Creating constrained types (odd integers, positive numbers, etc.)
- Data integrity is critical

### When to Use Cast Functions

Use cast functions when:
- Converting between types after creation
- Need runtime type conversion
- Building type adapters or wrappers
- Implementing polymorphic interfaces

### Naming Conventions

```walnut
/* Constructor error types: describe what went wrong */
InvalidProduct := ();
ProductNotFound := ();
InvalidRange := ();

/* Cast error types: use built-ins when possible */
NotANumber := ();         /* Built-in */
CastNotAvailable := [...]; /* Built-in */
```

### Error Messages

Provide clear error information:

```walnut
/* Good: specific error type */
ProductNotInCart := #[productId: ProductId];

/* Better: include context */
ValidationError := #[field: String, message: String, value: Any];

/* Best: include all relevant information */
RangeError := #[min: Integer, max: Integer, actual: Integer, message: String];
```

### Type Safety

Leverage the type system:

```walnut
/* Prefer specific types over Any */
ProductId := #Integer<1..>;  /* Good */

/* Avoid overly permissive types */
ProductId := Integer;  /* Less safe */

/* Use validators for constraints */
PositiveInteger := #Integer @ InvalidValue ::
    ?when(# <= 0) { => @InvalidValue };
```

## Summary

Walnut's constructor, validator, and cast system provides:

- **Constructors** - Convert types during creation with optional error handling
- **Validators** - Enforce invariants that always execute (even during hydration)
- **Cast Functions** - Convert between types after creation
- **Dependency Injection** - Both constructors and casts support dependencies
- **Error Handling** - Comprehensive Result-based error handling
- **Built-in Casts** - Standard conversions (asString, asInteger, asReal, asBoolean)
- **Dynamic Casts** - Runtime type conversion with Any->as(Type<T>)

These mechanisms work together to provide type-safe conversions with comprehensive error handling, enabling robust domain modeling and data validation.
