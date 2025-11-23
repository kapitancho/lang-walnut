# User-Defined Types

## Overview

Walnut provides a rich system for defining custom types that model domain logic precisely. All user-defined types can be enriched with behavior by adding methods and defining casts to/from other types. There are several different forms of user-defined types, each serving specific purposes:

- **Type Aliases** - Give new names to existing types and add behavior
- **Atoms** - Single-value types useful for sentinels and markers
- **Enumerations** - Types with a fixed set of named values
- **Enumeration Subsets** - Refined subsets of enumeration types
- **Data Types** - Lightweight wrappers with accessible underlying values
- **Open Types** - Distinct types with accessible underlying values
- **Sealed Types** - Encapsulated types with hidden internal structure

## Type Aliases

### Definition Syntax

Type aliases give new names to existing types, making code more readable and allowing methods and casts to be attached to the alias.

**Syntax:** `AliasName = ExistingType`

**Examples:**

```walnut
/* Simple aliases */
MyInt = Integer<1..10>;
Scalar = Real | String | Boolean | Null;
Point = [x: Real, y: Real];

/* From demo-all.nut */
MyAlias = Integer;

/* From shopping-cart/model.nut */
ProductId := String<1..>;
ProductTitle := String<1..>;
ProductPrice := Real<0..>;
ShoppingCartProduct = [id: ProductId, title: String, price: ProductPrice];

/* From http/message.nut */
HttpRequestTarget = String;
HttpResponseStatusCode = Integer<[101..103], [200..208], 226, [300..308], [400..418], 421, [422..426], 428, 429, 431, 451, [500..511]>;
HttpHeaders = Map<String:Array<String, 1..>>;
HttpMessageBody = String|Null;

/* Function type aliases */
LengthType = ^String => Integer;
ProductList = ^Null => *Array<Product>;
ProductById = ^ProductId => *Result<Product, UnknownProductId>;
```

### Adding Methods to Aliases

Once defined, aliases can have methods attached to them:

```walnut
/* Define alias */
MyPoint = [x: Real, y: Real];

/* Add method to calculate distance */
MyPoint->distanceTo(^MyPoint => Real) :: {{$x - #x} ** 2 + {$y - #y} ** 2}->sqrt;

/* Usage */
p = [x: 3.3, y: 1];
q = [x: 7.1, y: -2];
distance = p->distanceTo(q); /* 5 */
```

### Adding Casts to Aliases

Aliases can also define casts to other types:

```walnut
/* Define alias */
MyPoint = [x: Real, y: Real];

/* Add cast to String */
MyPoint ==> String :: ['(x: ', $x->asString, ', y:', $y->asString, ')']
    ->combineAsString('');

/* Usage */
p = [x: 3.3, y: 1];
s = p->asString; /* '(x: 3.3, y: 1.0)' */
```

### Key Characteristics

- **Transparent**: Values of an alias type are the same as values of the aliased type
- **Compile-time only**: The alias name is primarily for type checking
- **Methods require inference**: Methods and casts only work when the compiler can infer the alias type
- **Structural typing**: The underlying structure is still accessible

## Atoms

### Definition Syntax

Atoms are types that have exactly one value: the type itself. They are useful for representing unique markers, error types, and sentinel values.

**Syntax:** `AtomName := ()`

**Examples:**

```walnut
/* From demo-all.nut */
MyAtom := ();

/* From demo-constructor.nut */
E := ();
G := ();
J := ();
K := ();
Q := ();
R := ();

/* From shopping-cart/model.nut */
ProductNotInCart := #[productId: ProductId];
```

### Creating and Using Atom Values

```walnut
/* Define atom */
UnknownProduct := ();

/* Create atom value */
myProduct = UnknownProduct();

/* The atom value is the type itself */
myProduct = UnknownProduct; /* Also valid */
```

### Adding Methods to Atoms

Atoms can have methods attached to provide behavior:

```walnut
/* Define behavior for the atom type */
UnknownProduct->getProductTitle(=> String) :: 'n/a';

/* Usage */
myProduct = UnknownProduct();
title = myProduct->getProductTitle; /* 'n/a' */

/* From demo-all.nut */
MyAtom->myMethod(^String => Integer) %% MyAtom :: #->length;
```

### Adding Casts to Atoms

```walnut
/* Define a cast from atom to Boolean */
UnknownProduct ==> Boolean :: false;

/* Usage */
myProduct = UnknownProduct();
?when(myProduct->asBoolean)
    { 'The product is ok' }
  ~ { 'The product is unknown' }; /* Returns 'The product is unknown' */
```

### Use Cases

- **Error types**: Atoms are commonly used as error markers
- **Sentinel values**: Representing special states or conditions
- **Unique markers**: Type-safe constants that cannot be confused with other values
- **Empty responses**: Representing "nothing" in a type-safe way

### Key Characteristics

- **Single value**: Only one value exists for each atom type
- **Type-safe**: Cannot be confused with other types
- **Distinct**: Each atom type is unique and incompatible with other atom types
- **Zero runtime cost**: The value is effectively empty

## Enumerations

### Definition Syntax

Enumerations define types with a fixed set of named values. They are ideal for representing discrete options or states.

**Syntax:** `EnumName := (Value1, Value2, Value3, ...)`

**Examples:**

```walnut
/* From demo-all.nut */
MyEnum := (Value1, Value2, Value3);

/* From http/message.nut */
HttpProtocolVersion := (http_1_0, http_1_1, http_2, http_3);
HttpRequestMethod := (connect, delete, get, head, options, patch, post, put, trace);

/* From shopping-cart/model.nut */
ShirtSize := (S, M, L, XL, XXL);
ShirtColor := (Red, Green, Blue, Black, White);
MonitorResolution := (HD, FullHD, UHD);

/* Built-in enumeration */
Boolean := (true, false); /* Conceptually */
```

### Accessing Enumeration Values

Enumeration values are accessed using the type name followed by a dot and the value name:

**Syntax:** `EnumName.ValueName`

```walnut
/* Define enumeration */
Suit := (Clubs, Diamonds, Hearts, Spades);

/* Access values */
mySuit = Suit.Spades;
clubCard = Suit.Clubs;

/* From shopping-cart/model.nut */
shirtSize = ShirtSize.XL;
shirtColor = ShirtColor.Blue;
```

### Constructors for Enumerations

Enumerations can have constructors that create values from other types:

```walnut
/* Define enumeration */
HttpRequestMethod := (connect, delete, get, head, options, patch, post, put, trace);

/* Constructor can be defined (if needed) */
HttpRequestMethod(String) :: /* ... convert from string */
```

#### Default Constructor

If no constructor is explicitly defined, enumerations have a default constructor with the following behavior:

**Return type:**
- `Result<EnumType, UnknownEnumerationValue>` - when the parameter type is not known to be a valid enumeration value
- `EnumType` - when the parameter type is known to match one of the enumeration values

**Examples:**

```walnut
/* Define enumeration */
Suit := (Spade, Heart, Diamond, Club);

/* Default constructor behavior */
v1 = 'Spade';  /* Type: String['Spade'] */
result1 = Suit(v1);  /* Type: Suit[Spade] - no error possible */

v2 = 'Unknown';  /* Type: String['Unknown'] */
result2 = Suit(v2);  /* Type: Result<Suit, UnknownEnumerationValue> - might error */

v3 = 'Spade'->asString;  /* Type: String (general) */
result3 = Suit(v3);  /* Type: Result<Suit, UnknownEnumerationValue> */

/* When parameter type is a subset of valid values */
v4 = ?when(condition) { 'Spade' } ~ { 'Diamond' };
/* Type: String['Spade', 'Diamond'] */
result4 = Suit(v4);  /* Type: Suit[Spade, Diamond] - no error possible */

/* When parameter type includes invalid values */
v5 = ?when(condition) { 'Spade' } ~ { 'Invalid' };
/* Type: String['Spade', 'Invalid'] */
result5 = Suit(v5);  /* Type: Result<Suit, UnknownEnumerationValue> */
```

**Type inference rules:**
- If the parameter type is a **string subset** that contains **only** valid enumeration value names, the return type is `EnumType` (no Result wrapper)
- If the parameter type **might** contain invalid values, the return type is `Result<EnumType, UnknownEnumerationValue>`

Note: Custom constructors are optional and typically used when you need special conversion logic or validation beyond the default string-to-enumeration mapping.

### Adding Methods to Enumerations

```walnut
/* Define enumeration */
Suit := (Clubs, Diamonds, Hearts, Spades);

/* Add method to get suit color */
Suit->getSuitColor(=> String['black', 'red']) ::
    ?whenTypeOf($) is {
        `Suit[Clubs, Spades]: 'black',
        `Suit[Diamonds, Hearts]: 'red'
    };

/* Usage */
mySuit = Suit.Spades;
color = mySuit->getSuitColor; /* 'black' */
```

### Adding Casts to Enumerations

```walnut
/* Define cast from Suit to Integer */
Suit ==> Integer ::
    ?whenValueOf($) is {
        Suit.Clubs: 1,
        Suit.Diamonds: 2,
        Suit.Hearts: 3,
        Suit.Spades: 4
    };

/* Usage */
mySuit = Suit.Spades;
value = mySuit->asInteger; /* 4 */
```

### Key Characteristics

- **Fixed values**: The set of values is defined at type definition time
- **Named values**: Each value has a meaningful name
- **Type-safe**: Cannot be confused with strings or integers
- **Pattern matching**: Excellent support for exhaustive pattern matching
- **Distinct types**: Each enumeration type is unique

## Enumeration Subsets

### Definition Syntax

Enumeration subsets allow you to create refined types that only include specific values from an enumeration.
They can either be given a name as an Alias or be used anonymously in function and type signatures.

**Syntax:** `SubsetName = EnumName[Value1, Value2, ...]`

**Examples:**

```walnut
/* Define enumeration */
Suit := (Clubs, Diamonds, Hearts, Spades);

/* Define subsets */
RedSuit = Suit[Diamonds, Hearts];
BlackSuit = Suit[Clubs, Spades];

/* From demo-all.nut */
MyEnum := (Value1, Value2, Value3);
MyEnumSubset = MyEnum[Value1, Value2];
```

### Using Enumeration Subsets

```walnut
/* Define subset */
BlackSuit = Suit[Clubs, Spades];

/* Usage */
card = Suit.Spades; /* Type: Suit[Spades], can be assigned to BlackSuit */
card2 = Suit.Hearts; /* Type: Suit[Hearts] - Compilation error if assigned to BlackSuit */

/* Function accepting subset */
processBlackCard = ^suit: BlackSuit => String ::
    'Processing black suit: ' + suit->asString;
```

### Type Relationships

```walnut
Suit := (Clubs, Diamonds, Hearts, Spades);
RedSuit = Suit[Diamonds, Hearts];

/* Type relationships: */
/* - RedSuit is a subtype of Suit */
/* - Suit.Diamonds is a value of type RedSuit */
/* - Suit.Diamonds is also a value of type Suit */
```

### Key Characteristics

- **Refined types**: Subsets are more specific than the parent enumeration
- **Subtyping**: Subset types are subtypes of the parent enumeration
- **Type safety**: Prevents invalid values at compile time
- **Domain modeling**: Excellent for representing valid state transitions

## Data Types

### Definition Syntax

Data types are lightweight wrappers around other types. They create distinct types but keep the underlying value fully accessible. Unlike open types, data types have no constructor and can be used in constant expressions.

**Syntax:**
- `DataName := BaseType`

**Creation:**
- `DataName!value` - Direct construction with `!` operator

**Examples:**

```walnut
/* Simple data type */
MyData := Integer;

/* From demo-type.nut */
ProductId := #Integer<1..>;

/* From pp/model.nut */
ProductId := # Integer<1..>;
ProductName := # String<1..>;
ProductPrice := # Real<0..>;
Product := #[id: ProductId, name: ProductName, price: ProductPrice];
UnknownProductId := # ProductId;

/* From shopping-cart/model.nut */
ProductNotInCart := #[productId: ProductId];
```

### Creating Data Type Values

Data types are created using the `!` operator:

```walnut
/* Define data type */
ProductId := #Integer<1..>;

/* Create value using ! */
id = ProductId!42;

/* Cannot use constructor syntax */
id2 = ProductId(42); /* Error - data types have no constructor */
```

### Accessing Underlying Values

Data types expose their underlying value directly:

```walnut
ProductId := #Integer<1..>;
ProductPrice := #Real<0..>;

id = ProductId!42;
price = ProductPrice!29.99;

/* Access underlying value */
idValue = id->value; /* 42 */
priceValue = price->value; /* 29.99 */

/* Can also use $$ in methods */
ProductId ==> String :: $$->asString;
```

### Use in Constant Expressions

Unlike open types, data types can be used in constant expressions:

```walnut
/* Define data type */
ProductId := #Integer<1..>;

/* This is allowed (constant expression) */
allConstants = [
    data: ProductId!42
];

/* Open types cannot be used in constant expressions */
MyOpen := #[a: Integer];
allConstants2 = [
    open: MyOpen[a: 42] /* Error in constant context */
];
```

### Key Characteristics

- **No constructor**: Created directly with `!` operator
- **Accessible value**: Underlying value is fully accessible
- **Distinct type**: ProductId!42 is different from Integer 42
- **Constant expressions**: Can be used in constant/compile-time contexts
- **Lightweight**: Minimal runtime overhead
- **Type safety**: Cannot be confused with the base type

### When to Use Data Types

Use data types when you want:
- Distinct types without encapsulation
- Access to the underlying value
- Usage in constant expressions
- Minimal ceremony (no constructors needed)
- Type-safe IDs, codes, or labels

## Open Types

### Definition Syntax

Open types create distinct types where the underlying value is accessible. They support constructors and validators, allowing controlled creation while maintaining transparency.

**Syntax:**
- `OpenName := #BaseType`
- `OpenName := #BaseType @ ErrorType :: validatorExpression` (with validator)

**Examples:**

```walnut
/* Simple open type */
MyOpen0 := #[a: Integer, b: Integer];

/* From demo-all.nut */
MyOpen := #[a: Integer, b: Integer] @ MyAtom :: null;
MyOpen1 := #[a: Integer, b: Integer];

/* From demo-constructor.nut */
P := #[x: Integer, y: Integer] @ Q :: {
    z = #y - #x;
    ?whenTypeOf(z) is { `Integer<10..>: null, ~: => @Q }
};

/* From demo-type.nut */
ProductEvent := #[title: String, price: Real];
```

### Creating Open Type Values

Open types are created using bracket syntax:

```walnut
MyOpen0 := #[a: Integer, b: Integer];

/* Create value */
obj = MyOpen0[a: 3, b: -2];

/* Access properties directly */
aValue = obj.a; /* 3 */
bValue = obj.b; /* -2 */

/* Access underlying value */
baseValue = obj->value; /* [a: 3, b: -2] */
```

### Constructors

Open types can have constructors that convert from other types:

```walnut
/* Define open type */
MyOpen1 := #[a: Integer, b: Integer];

/* Define constructor */
MyOpen1[a: Real, b: Real] :: [a: #a->asInteger, b: #b->asInteger];

/* From demo-all.nut */
MyOpen[a: Real, b: Real] %% MyAtom :: [a: #a->asInteger, b: #b->asInteger];

/* Usage */
obj = MyOpen1[a: 3.7, b: -2.3]; /* Converts Reals to Integers */
/* Result: MyOpen1[a: 3, b: -2] */
```

### Validators

Validators ensure that values satisfy certain invariants:

```walnut
/* Define error type */
Q := ();

/* Define open type with validator */
P := #[x: Integer, y: Integer] @ Q :: {
    z = #y - #x;
    ?whenTypeOf(z) is {
        `Integer<10..>: null,
        ~: => @Q
    }
};

/* Usage */
p1 = P[x: 5, y: 20]; /* OK: 20 - 5 = 15 (>= 10) */
p2 = P[x: 5, y: 10]; /* Error: 10 - 5 = 5 (< 10) */
```

### Constructor with Validator

Both can be defined together:

```walnut
J := ();
K := ();

L := $[x: Integer, y: Integer] @ K :: {
    z = #y - #x;
    ?whenTypeOf(z) is { `Integer<1..>: null, ~: => @K }
};

L[t: Integer, u: Integer] @ J :: {
    ?whenTypeOf(#u) is { `Integer<1..>: null, ~: => @J };
    [x: #t, y: #u]
};

/* From demo-constructor.nut */
P := #[x: Integer, y: Integer] @ Q :: {
    z = #y - #x;
    ?whenTypeOf(z) is { `Integer<10..>: null, ~: => @Q }
};

P[t: String, u: Integer] @ R :: {
    z = #u - #t->length;
    ?whenTypeOf(z) is { `Integer<1..>: null, ~: => @R };
    [x: #t->length, y: #u]
};

/* Usage */
pq = ^Null => Result<P, Q|R> :: P[t: 'hello', u: 7];
/* Constructor converts String to length, validator checks difference */
```

### Property Access

Open types allow direct property access:

```walnut
ProductEvent := #[title: String, price: Real];

event = ProductEvent[title: 'Product Launch', price: 49.99];

/* Access properties directly */
title = event.title; /* 'Product Launch' */
price = event.price; /* 49.99 */
```

### Key Characteristics

- **Accessible value**: Properties and underlying value are accessible
- **Distinct type**: Values are different from the base type
- **Constructors**: Optional constructors for conversion
- **Validators**: Optional invariant checking with error returns
- **Property access**: Direct access to properties
- **Type safety**: Separate from base type in function signatures

### When to Use Open Types

Use open types when you want:
- Distinct types with controlled creation
- Access to properties or underlying values
- Validation of invariants
- Custom construction logic
- Transparent data with type distinction

## Sealed Types

### Definition Syntax

Sealed types encapsulate their internal structure completely. Properties cannot be accessed directly; access is only possible through methods defined on the type. This provides full encapsulation similar to OOP classes.

**Syntax:**
- `SealedName := $BaseType`
- `SealedName := $BaseType @ ErrorType :: validatorExpression` (with validator)

**Examples:**

```walnut
/* Simple sealed type */
MySealed0 := $[a: Integer, b: Integer];

/* From demo-all.nut */
MySealed := $[a: Integer, b: Integer] @ MyAtom :: null;
MySealed1 := $[a: Integer, b: Integer];

/* From demo-constructor.nut */
C := $[x: Integer, y: Integer];
D := $[x: Integer, y: Integer];
H := $[x: Integer, y: Integer] @ G :: {
    z = #y - #x;
    ?whenTypeOf(z) is { `Integer<1..>: null, ~: => @G }
};

/* From demo-type.nut */
ProductState := $[title: String, price: Real, quantity: Integer];

/* From shopping-cart/model.nut */
ShoppingCart := $[items: Mutable<Map<ShoppingCartItem>>];
Shirt := $[id: ProductId, model: ProductTitle, price: ProductPrice, size: ShirtSize, color: ShirtColor];
Monitor := $[id: ProductId, brand: BrandName, model: ProductTitle, price: ProductPrice, resolution: MonitorResolution, sizeInInches: MonitorSize];
Wine := $[id: ProductId, producer: WineProducer, name: ProductTitle, price: ProductPrice, year: WineProductionYear];
```

### Creating Sealed Type Values

Sealed types are created using bracket syntax:

```walnut
MySealed0 := $[a: Integer, b: Integer];

/* Create value */
obj = MySealed0[a: 3, b: -2];

/* Cannot access properties directly */
aValue = obj.a; /* Compilation error - sealed type */

/* Can only access via methods */
```

### Accessing Properties via Methods

The `$` variable in methods refers to the sealed value:

```walnut
/* Define sealed type */
Product := $[id: Integer, name: String, price: Real];

/* Define methods to access properties */
Product->id(=> Integer) :: $id;
Product->name(=> String) :: $name;
Product->price(=> Real) :: $price;

/* Update method */
Product->updatePrice(^Real) :: [id: $id, name: $name, price: #];

/* Usage */
myProduct = Product[1, 'Bread', 1.5];
productName = myProduct->name; /* 'Bread' */
productId = myProduct->id; /* 1 */
```

### Mutable State in Sealed Types

Sealed types can encapsulate mutable state:

```walnut
/* Define sealed type with mutable property */
Product := $[id: Integer, name: String, price: Mutable<Real>];

/* Define update method */
Product->updatePrice(^Real) :: $price->SET(#);

/* Usage */
myProduct = Product[1, 'Bread', mutable{Real, 1.5}];
myProduct->updatePrice(1.6);
```

### Constructors

Sealed types support constructors:

```walnut
/* Define sealed type */
MySealed1 := $[a: Integer, b: Integer];

/* Define constructor from Real values */
MySealed1[a: Real, b: Real] :: [a: #a->asInteger, b: #b->asInteger];

/* From demo-all.nut */
MySealed[a: Real, b: Real] %% MyAtom :: [a: #a->asInteger, b: #b->asInteger];

/* From shopping-cart/model.nut */
ShoppingCart := $[items: Mutable<Map<ShoppingCartItem>>];
ShoppingCart() :: [items: mutable{Map<ShoppingCartItem>, [:]}];

/* Usage */
cart = ShoppingCart(); /* No-argument constructor */
obj = MySealed1[a: 3.7, b: -2.3];
```

### Validators

Validators ensure invariants are maintained:

```walnut
/* Define error type */
G := ();

/* Define sealed type with validator */
H := $[x: Integer, y: Integer] @ G :: {
    z = #y - #x;
    ?whenTypeOf(z) is { `Integer<1..>: null, ~: => @G }
};

/* Usage */
h1 = H[3, 8]; /* OK: 8 - 3 = 5 (>= 1) */
h2 = H[7, 4]; /* Error: 4 - 7 = -3 (< 1) */
```

### Constructor with Validator

```walnut
K := ();
J := ();

L := $[x: Integer, y: Integer] @ K :: {
    z = #y - #x;
    ?whenTypeOf(z) is { `Integer<1..>: null, ~: => @K }
};

/* Constructor with its own validator */
L[t: Integer, u: Integer] @ J :: {
    ?whenTypeOf(#u) is { `Integer<1..>: null, ~: => @J };
    [x: #t, y: #u]
};

/* From demo-constructor.nut */
D := $[x: Integer, y: Integer];
D[t: Integer, u: Integer] @ E :: {
    z = #u - #t;
    ?whenTypeOf(z) is { `Integer<1..>: null, ~: => @E };
    [x: #t, y: #u]
};
```

### Encapsulation Example

```walnut
/* Define sealed type */
Product := $[id: Integer, name: String, price: Mutable<Real<0..>>];

/* Methods for controlled access */
Product->updatePrice(^Real) :: $price->SET(#);
Product->name(=> String) :: $name;
Product->id(=> Integer) :: $id;
Product->price(=> Real) :: $price->value;

/* Usage */
myProduct = Product[1, 'Bread', mutable{Real<0..>, 1.5}];
myProduct->updatePrice(1.6);
myProductName = myProduct->name; /* 'Bread' */
currentPrice = myProduct->price; /* 1.6 */

/* Cannot pass sealed type where base type is expected */
myPointFn = ^[x: Real, y: Real] => Any :: /* ... some function */

Point := $[x: Real, y: Real];
myPoint = Point[3.3, 1];
myPointFn(myPoint); /* Compilation error - sealed type not compatible */
```

### Casts from Sealed Types

Sealed types can define casts to other types:

```walnut
/* From shopping-cart/model.nut */
Shirt := $[id: ProductId, model: ProductTitle, price: ProductPrice, size: ShirtSize, color: ShirtColor];

Shirt ==> ShoppingCartProduct :: [
    id: $id,
    title: $model->asString + ' ' + {$color->asString} + ' ' + {$size->asString},
    price: $price
];

Monitor := $[id: ProductId, brand: BrandName, model: ProductTitle, price: ProductPrice, resolution: MonitorResolution, sizeInInches: MonitorSize];

Monitor ==> ShoppingCartProduct :: [
    id: $id,
    title: {$brand->asString} + ' ' + {$model->asString} + ' ' + {$resolution->asString} + ' ' + $sizeInInches->value->asString + '"',
    price: $price
];
```

### Key Characteristics

- **Encapsulation**: Internal structure is completely hidden
- **Method-only access**: Properties accessible only through methods
- **Distinct type**: Values are incompatible with base type
- **Constructors**: Optional constructors for conversion
- **Validators**: Optional invariant checking with error returns
- **$ variable**: Methods use `$` to access the sealed value
- **Mutable state**: Can safely encapsulate mutable references
- **Type safety**: Cannot be passed where base type is expected

### When to Use Sealed Types

Use sealed types when you want:
- Full encapsulation of data
- Controlled access to properties
- Mutable internal state with invariants
- OOP-style classes with methods
- Strong separation between interface and implementation
- Domain objects with business logic

## Type Definition Syntax Summary

### Quick Reference

| Type Form | Syntax | Constructor | Validator | Property Access | Use Case |
|-----------|--------|------------|-----------|----------------|----------|
| **Type Alias** | `Name = Type` | N/A | N/A | Yes (transparent) | Naming existing types |
| **Atom** | `Name := ()` | N/A | N/A | N/A (single value) | Sentinels, markers, error types |
| **Enumeration** | `Name := (V1, V2, ...)` | Optional | N/A | N/A (fixed values) | Discrete options |
| **Enum Subset** | `Name = Enum[V1, V2]` | N/A | N/A | N/A (fixed values) | Refined enumerations |
| **Data Type** | `Name := #Type` | No (use `!`) | No | Yes (via `.property`) | Lightweight distinct types |
| **Open Type** | `Name := #Type` | Optional | Optional | Yes (via `.property`) | Distinct types with validation |
| **Sealed Type** | `Name := $Type` | Optional | Optional | No (methods only) | Encapsulated objects |

### Syntax Patterns

```walnut
/* Type Alias */
AliasName = ExistingType;

/* Atom */
AtomName := ();

/* Enumeration */
EnumName := (Value1, Value2, Value3);

/* Enumeration Subset */
SubsetName = EnumName[Value1, Value2];

/* Data Type */
DataName := BaseType;
value = DataName!baseValue;

/* Open Type (simple) */
OpenName := #BaseType;
value = OpenName[...];

/* Open Type (with constructor) */
OpenName := #BaseType;
OpenName[params] :: constructorExpression;

/* Open Type (with validator) */
OpenName := #BaseType @ ErrorType :: validatorExpression;

/* Open Type (constructor + validator) */
OpenName := #BaseType @ ErrorType1 :: validatorExpression;
OpenName[params] @ ErrorType2 :: constructorExpression;

/* Sealed Type (simple) */
SealedName := $BaseType;
value = SealedName[...];

/* Sealed Type (with constructor) */
SealedName := $BaseType;
SealedName[params] :: constructorExpression;

/* Sealed Type (with validator) */
SealedName := $BaseType @ ErrorType :: validatorExpression;

/* Sealed Type (constructor + validator) */
SealedName := $BaseType @ ErrorType1 :: validatorExpression;
SealedName[params] @ ErrorType2 :: constructorExpression;
```

### Value Creation Patterns

```walnut
/* Alias - same as base type */
alias = 42; /* Type: Integer[42], can be assigned to MyAlias */

/* Atom - type itself or called */
atom1 = MyAtom;
atom2 = MyAtom();

/* Enumeration - dot notation */
enum = MyEnum.Value1;

/* Enumeration Subset - same as enumeration */
subset = MyEnum.Value1;

/* Data Type - using ! operator */
data = MyData!42;

/* Open Type - bracket construction */
open = MyOpen[a: 1, b: 2];

/* Sealed Type - bracket construction */
sealed = MySealed[a: 1, b: 2];
```

## Advanced Topics

### Validators and Constructors

Both validators and constructors can return errors:

**Validator errors** indicate that a value violates the type's invariants:

```walnut
ErrorType := ();
TypeName := #BaseType @ ErrorType :: {
    /* Validation logic */
    ?when(condition) { => @ErrorType }
};
```

**Constructor errors** indicate that the input cannot be converted:

```walnut
ConstructorError := ();
TypeName[InputType] @ ConstructorError :: {
    /* Conversion logic with validation */
    ?when(invalid) { => @ConstructorError };
    /* Return constructed value */
    [...]
};
```

### Hydration vs Construction

- **Construction** uses constructors (can be bypassed with `hydrateAs`)
- **Validation** always runs, even with `hydrateAs`

```walnut
D := $[x: Integer, y: Integer];
D[t: Integer, u: Integer] @ E :: {
    z = #u - #t;
    ?whenTypeOf(z) is { `Integer<1..>: null, ~: => @E };
    [x: #t, y: #u]
};

/* Using constructor */
d1 = D[3, 8]; /* OK */

/* Using hydration (bypasses constructor) */
d2 = [x: 3, y: 8]->hydrateAs(`D); /* Still runs validator */
```

### Dependency Injection in Constructors

Constructors can use dependency injection:

```walnut
/* Constructor with dependencies */
MyType[params] %% [~ServiceName] :: {
    /* Use %serviceName */
    constructorBody
};

/* From demo-all.nut */
MyOpen[a: Real, b: Real] %% MyAtom :: [a: #a->asInteger, b: #b->asInteger];
```

### The `$` Variable in Methods

- For **aliases**: `$` refers to the value itself
- For **open types**: `$` refers to the wrapped value (can also use `.property`)
- For **sealed types**: `$` is the only way to access the underlying value

```walnut
/* Alias method */
MyPoint = [x: Real, y: Real];
MyPoint->sum(=> Real) :: $x + $y;

/* Open type method */
MyOpen := #[a: Integer];
MyOpen->doubled(=> Integer) :: $a * 2; /* or $a * 2 */

/* Sealed type method */
MySealed := $[a: Integer];
MySealed->doubled(=> Integer) :: $a * 2; /* Only way to access */
```

### Type Introspection

All user-defined types support introspection:

```walnut
/* Get type name */
ProductId := #Integer<1..>;
name = `ProductId->typeName; /* 'ProductId' */

/* Get underlying type */
ProductEvent := #[title: String, price: Real];
valueType = `ProductEvent->valueType; /* [title: String, price: Real] */

/* From demo-type.nut */
reflectOpen = ^Type<Open> => [String, Type] :: [#->typeName, #->valueType];
reflectSealed = ^Type<Sealed> => [String, Type] :: [#->typeName, #->valueType];
```

## Best Practices

### Choosing the Right Type Form

1. **Use Type Aliases** when:
   - You want to name a complex type for readability
   - You need to add methods to existing types
   - The type is structurally transparent

2. **Use Atoms** when:
   - You need unique marker values
   - Creating error types
   - Representing special states or sentinels

3. **Use Enumerations** when:
   - You have a fixed set of named options
   - Values are mutually exclusive
   - You need exhaustive pattern matching

4. **Use Enumeration Subsets** when:
   - You need a refined subset of an enumeration
   - Modeling valid state transitions
   - Creating more specific type constraints

5. **Use Data Types** when:
   - You need distinct types with minimal ceremony
   - The underlying value should be accessible
   - Usage in constant expressions is required
   - No construction logic or validation is needed

6. **Use Open Types** when:
   - You need distinct types with validation
   - The underlying value should be accessible
   - You want constructors for conversion
   - Properties need to be accessed directly

7. **Use Sealed Types** when:
   - You need full encapsulation
   - Internal structure should be hidden
   - You want OOP-style classes with methods
   - Managing mutable state with invariants

### Naming Conventions

```walnut
/* Type names: PascalCase */
ProductId := #Integer<1..>;
ShoppingCart := $[items: Map];
HttpRequestMethod := (get, post, put);

/* Enumeration values: camelCase or PascalCase */
ShirtSize := (S, M, L, XL, XXL);
HttpProtocolVersion := (http_1_0, http_1_1, http_2);
```

### Error Type Patterns

```walnut
/* Error types are often atoms or open types */
ProductNotFound := ();
ValidationError := #[field: String, message: String];

/* Use in Result types */
findProduct = ^id: ProductId => Result<Product, ProductNotFound> :: ...;
validateUser = ^user: User => Result<User, ValidationError> :: ...;
```

## Summary

Walnut's user-defined type system provides a spectrum of options from transparent aliases to fully encapsulated sealed types:

- **Type Aliases** add names and behavior to existing types
- **Atoms** create unique single-value types
- **Enumerations** define fixed sets of named values
- **Enumeration Subsets** refine enumerations further
- **Data Types** create lightweight distinct types with accessible values
- **Open Types** provide distinct types with validation and accessible values
- **Sealed Types** offer full encapsulation with method-based access

Each form serves specific needs in domain modeling, from simple type aliases to complex OOP-style objects with encapsulated state. The choice depends on the level of encapsulation, validation, and type distinction required for your domain model.
