# 5. Type Refinement

## Overview

Type refinement is a powerful feature in Walnut that allows constraining types to specific ranges, lengths, or sets of values. This enables precise domain modeling and compile-time validation of business rules.

## 5.1 Integer Refinement

### 5.1.1 Range Constraints

Integer types can be refined with inclusive range constraints.

**Syntax:**
- `Integer<min..max>` - integers from min to max (inclusive)
- `Integer<min..>` - integers from min onwards
- `Integer<..max>` - integers up to max
- `Integer` - all integers (equivalent to `Integer<..>`)

**Examples:**
```walnut
/* Age constraint */
Age = Integer<0..150>;

/* Positive integers */
PositiveInt = Integer<1..>;

/* Non-positive integers */
NonPositiveInt = Integer<..0>;

/* Temperature in Celsius */
RoomTemperature = Integer<15..30>;

/* Single value range */
FortyTwo = Integer<42..42>;
```

**Type inference:**
```walnut
x = 42;           /* Type: Integer[42] */
y = 50;  /* Type: Integer[50], can be assigned to Integer<1..100> */
```

### 5.1.2 Integer Subsets

Integer subsets define finite sets of allowed integer values.

**Syntax:** `Integer[value1, value2, value3, ...]`

**Examples:**
```walnut
/* Dice roll values */
DiceRoll = Integer[1, 2, 3, 4, 5, 6];

/* Prime single digits */
PrimeDigit = Integer[2, 3, 5, 7];

/* HTTP success status codes */
HttpSuccess = Integer[200, 201, 202, 204];

/* Powers of two */
PowersOfTwo = Integer[1, 2, 4, 8, 16, 32, 64, 128, 256];
```

**Usage:**
```walnut
roll = 5;  /* Type: Integer[5], can be assigned to DiceRoll */
prime = 7;  /* Type: Integer[7], can be assigned to PrimeDigit */

/* Compile error: 9 is not in PrimeDigit */
/* invalid = 9; assigned to PrimeDigit would fail */
```

### 5.1.3 Subtyping Relationships

```walnut
/* Integer subsets are subtypes of ranges */
Integer[5] <: Integer<1..10>          /* true */
Integer[1, 2, 3] <: Integer<1..10>    /* true */

/* Ranges are subtypes of broader ranges */
Integer<5..10> <: Integer<1..20>      /* true */
Integer<1..> <: Integer                /* true */
```

## 5.2 Real Refinement

### 5.2.1 Simple Range Constraints

Real types support range constraints with closed intervals by default.

**Syntax:**
- `Real<min..max>` - reals from min to max (closed interval [min, max])
- `Real<min..>` - reals from min onwards
- `Real<..max>` - reals up to max
- `Real` - all reals (equivalent to `Real<..>`)

**Examples:**
```walnut
/* Temperature in Kelvin (absolute zero and above) */
TemperatureKelvin = Real<0..>;

/* Probability */
Probability = Real<0..1>;

/* Percentage */
Percentage = Real<0..100>;

/* Price */
Price = Real<0..>;
```

### 5.2.2 Advanced Range Syntax

Real ranges support open and closed interval endpoints and multiple disjoint intervals.

**Interval notation:**
- `[min..max]` - closed interval (includes both endpoints)
- `(min..max)` - open interval (excludes both endpoints)
- `[min..max)` - half-open interval (includes min, excludes max)
- `(min..max]` - half-open interval (excludes min, includes max)

**Examples:**
```walnut
/* Exclude zero */
NonZero = Real<(..-0), (0..)>;
/* Equivalent to: (-∞, 0) ∪ (0, +∞) */

/* Multiple intervals */
ValidRange = Real<(..-2], [1..5.29), (6.43..)>;
/* Equivalent to: (-∞, -2] ∪ [1, 5.29) ∪ (6.43, +∞) */

/* Unit interval (closed) */
UnitInterval = Real<[0..1]>;

/* Open unit interval */
OpenUnitInterval = Real<(0..1)>;

/* Positive reals (excluding zero) */
PositiveReal = Real<(0..)>;
```

### 5.2.3 Real Subsets

Real subsets define finite sets of allowed real values.

**Syntax:** `Real[value1, value2, value3, ...]`

**Examples:**
```walnut
/* Mathematical constants */
MathConstants = Real[3.14159, 2.71828, 1.61803];

/* Benchmark values */
Benchmarks = Real[0.0, 0.5, 1.0];

/* Temperature checkpoints */
Checkpoints = Real[-40.0, 0.0, 37.0, 100.0];
```

**Usage:**
```walnut
pi = 3.14159;  /* Type: Real[3.14159] */
e = 2.71828;  /* Type: Real[2.71828], can be assigned to MathConstants */
```

## 5.3 String Refinement

### 5.3.1 Length Constraints

String types can be refined with length constraints based on UTF-8 character count.

**Syntax:**
- `String<min..max>` - strings with length from min to max
- `String<min..>` - strings with minimum length
- `String<..max>` - strings with maximum length
- `String<length>` - strings with exact length (equivalent to `String<length..length>`)
- `String` - all strings (equivalent to `String<..>`)

**Examples:**
```walnut
/* Email address */
Email = String<5..254>;

/* Password (at least 8 characters) */
Password = String<8..>;

/* Product name */
ProductName = String<1..100>;

/* Two-letter country code */
CountryCode = String<2>;

/* Non-empty string */
NonEmptyString = String<1..>;

/* Short string (up to 10 characters) */
ShortString = String<..10>;
```

**Type inference:**
```walnut
greeting = 'Hello';    /* Type: String['Hello'] with length 5 */
empty = '';            /* Type: String[''] with length 0 */
```

### 5.3.2 String Subsets

String subsets define finite sets of allowed string values.

**Syntax:** `String['value1', 'value2', 'value3', ...]`

**Examples:**
```walnut
/* Compass directions */
Direction = String['north', 'south', 'east', 'west'];

/* HTTP methods */
HttpMethod = String['GET', 'POST', 'PUT', 'DELETE', 'PATCH'];

/* Log levels */
LogLevel = String['debug', 'info', 'warning', 'error', 'fatal'];

/* Status values */
Status = String['pending', 'active', 'completed', 'cancelled'];

/* Single value */
HelloWorld = String['hello world'];
```

**Usage:**
```walnut
direction = 'north';  /* Type: String['north'], can be assigned to Direction */
method = 'POST';  /* Type: String['POST'], can be assigned to HttpMethod */
status = 'active';  /* Type: String['active'], can be assigned to Status */

/* Compile error: 'northeast' is not in Direction */
/* invalid = 'northeast'; assigned to Direction would fail */
```

**Combined with length constraints:**
```walnut
/* String subset automatically has length constraints */
Direction = String['north', 'south', 'east', 'west'];
/* All values have length between 4 and 5 */
/* So Direction <: String<4..5> */
```

## 5.4 Collection Refinement

### 5.4.1 Array Length Constraints

Arrays can be refined with element type and length constraints.

**Syntax:**
- `Array<ElementType, min..max>` - array with element type and length
- `Array<ElementType, min..>` - array with element type and minimum length
- `Array<ElementType, ..max>` - array with element type and maximum length
- `Array<ElementType>` - array with element type, any length
- `Array<min..max>` - array with any element type and length constraint
- `Array` - equivalent to `Array<Any>`

**Examples:**
```walnut
/* Array of 1-10 integers */
IntArray = Array<Integer, 1..10>;

/* Non-empty array of strings */
NonEmptyStrings = Array<String, 1..>;

/* Up to 5 items */
SmallArray = Array<Any, ..5>;

/* Exactly 3 elements */
Triple = Array<Integer, 3..3>;

/* Combining constraints */
Usernames = Array<String<3..20>, 1..100>;
```

**Type inference:**
```walnut
values = [1, 2, 3];
/* Type: [Integer[1], Integer[2], Integer[3]] */
/* Subtype of: Array<Integer, 3..3> */
```

### 5.4.2 Map Length Constraints

Maps can be refined with key type, value type, and length constraints.

**Syntax:**
- `Map<T, min..max>` - map with value type T and length constraint (string keys)
- `Map<K:T, min..max>` - map with key type K, value type T, and length constraint
- `Map<T, min..>` - map with value type T and minimum length (string keys)
- `Map<K:T, min..>` - map with key type K, value type T, and minimum length
- `Map<T, ..max>` - map with value type T and maximum length (string keys)
- `Map<K:T, ..max>` - map with key type K, value type T, and maximum length
- `Map<T>` - map with value type T, any length (string keys)
- `Map<K:T>` - map with key type K and value type T, any length
- `Map<min..max>` - map with any value type and length constraint (string keys)
- `Map` - equivalent to `Map<String:Any>`

**Examples:**
```walnut
/* Configuration with 1-20 entries (string keys) */
Config = Map<String, 1..20>;

/* Small lookup table with integer values */
LookupTable = Map<Integer, ..10>;

/* Non-empty settings */
Settings = Map<String|Integer|Boolean, 1..>;

/* User metadata */
UserMetadata = Map<String<..100>, ..50>;

/* Map with specific key values */
StatusCode = String['success', 'pending', 'error'];
StatusMap = Map<StatusCode:String, 1..3>;

/* Map with refined key type */
ServiceName = String<1..50>;
ServiceMap = Map<ServiceName:Integer, ..100>;
```

### 5.4.3 Set Length Constraints

Sets can be refined with element type and length constraints.

**Syntax:**
- `Set<ElementType, min..max>` - set with element type and length
- `Set<ElementType, min..>` - set with element type and minimum length
- `Set<ElementType, ..max>` - set with element type and maximum length
- `Set<ElementType>` - set with element type, any length
- `Set<min..max>` - set with any element type and length constraint
- `Set` - equivalent to `Set<Any>`

**Examples:**
```walnut
/* Set of 1-10 unique tags */
Tags = Set<String, 1..10>;

/* Non-empty set of IDs */
IdSet = Set<Integer<1..>, 1..>;

/* Small set */
SmallSet = Set<Any, ..5>;

/* Role permissions */
Permissions = Set<String['read', 'write', 'delete'], 1..3>;
```

## 5.5 Enumeration Subsets

Enumeration types can be refined to contain only a subset of the enumeration values.

**Syntax:** `EnumerationType[Value1, Value2, ...]`

**Examples:**
```walnut
/* Define enumeration */
Suit := (Clubs, Diamonds, Hearts, Spades);

/* Define subsets */
RedSuit = Suit[Diamonds, Hearts];
BlackSuit = Suit[Clubs, Spades];

/* Usage */
redCard = Suit.Hearts;  /* Type: Suit[Hearts], can be assigned to RedSuit */
blackCard = Suit.Clubs;  /* Type: Suit[Clubs], can be assigned to BlackSuit */

/* Another example */
DayOfWeek := (Monday, Tuesday, Wednesday, Thursday, Friday, Saturday, Sunday);
Weekday = DayOfWeek[Monday, Tuesday, Wednesday, Thursday, Friday];
Weekend = DayOfWeek[Saturday, Sunday];

today = DayOfWeek.Monday;  /* Type: DayOfWeek[Monday], can be assigned to Weekday */
```

**Subtyping:**
```walnut
RedSuit <: Suit           /* true */
BlackSuit <: Suit         /* true */
Suit[Hearts] <: RedSuit   /* true */
```

## 5.6 Combining Refinements

### 5.6.1 Union Types with Refinements

```walnut
/* Optional constrained value */
OptionalAge = Integer<0..150>|Null;

/* Multiple ranges */
Temperature = Integer<-40..0>|Integer<20..40>;

/* Mixed constraints */
Id = Integer<1..>|String<5..50>;
```

### 5.6.2 Intersection Types with Refinements

Intersections work primarily with records and structural types.

```walnut
/* Record with both constraints */
ValidUser = [id: Integer<1..>, ...]
          & [email: String<5..>, ...]
          & [age: Integer<18..>, ...];

/* Narrowing ranges through intersection */
NarrowRange = Integer<1..100> & Integer<50..150>;
/* Result: Integer<50..100> */
```

## 5.7 Practical Examples

### 5.7.1 Domain Modeling

```walnut
/* E-commerce types */
ProductId := Integer<1..>;
Quantity := Integer<1..1000>;
Price := Real<0..>;
ProductName := String<1..200>;
Description := String<..5000>;

Product := $[
    id: ProductId,
    name: ProductName,
    price: Price,
    description: Description
];

/* Order line item */
OrderLine := $[
    productId: ProductId,
    quantity: Quantity,
    unitPrice: Price
];
```

### 5.7.2 Validation Functions

```walnut
/* Validate age */
validateAge = ^age: Integer => Result<Integer<0..150>, String> ::
    ?when(age >= 0 && age <= 150) {
        age
    } ~ {
        @'Age must be between 0 and 150'
    };

/* Validate email length */
validateEmail = ^email: String => Result<String<5..254>, String> ::
    ?when(email->length >= 5 && email->length <= 254) {
        email
    } ~ {
        @'Email must be between 5 and 254 characters'
    };
```

### 5.7.3 Range Operations

```walnut
/* Integer range operations (from demo-range.nut) */
simpleRange = IntegerRange[1, 10];
openEndedRange = IntegerRange[100, PlusInfinity];
negativeRange = IntegerRange[MinusInfinity, 0];
fullRange = IntegerRange[MinusInfinity, PlusInfinity];

/* Real range with intervals */
complexRange = RealNumberRange![intervals: [
    RealNumberInterval[
        start: MinusInfinity,
        end: RealNumberIntervalEndpoint![value: -17.3, inclusive: true]
    ]?,
    RealNumberInterval[
        start: RealNumberIntervalEndpoint![value: 3.14, inclusive: true],
        end: RealNumberIntervalEndpoint![value: 10, inclusive: false]
    ]?
]];
```

### 5.7.4 Type Narrowing in Functions

```walnut
/* Function with refined parameter */
calculateDiscount = ^price: Price => Real<0..> ::
    ?when(price > 100) {
        price * 0.1  /* 10% discount */
    } ~ {
        0
    };

/* Function returning refined type */
getPositiveValue = ^x: Integer => Integer<1..> ::
    ?when(x > 0) {
        x
    } ~ {
        1  /* Default to 1 */
    };
```

## 5.8 Type Refinement in Methods

User-defined types can have validators that enforce refinements at runtime.

**Example:**
```walnut
/* Define refined open type with validation */
PositivePrice := #Real<0..>;

/* Validator automatically checks range */
PositivePrice(price: Real) @ String ::
    ?whenTypeOf(price) {
        `Real<0..>: price,
        ~ : @'Price must be positive'
    };
```

## 5.9 Best Practices

### 5.9.1 Use Refinements for Business Rules

```walnut
/* Good: Encode business rules in types */
Age := Integer<0..150>;
Email := String<5..254>;
PhoneNumber := String<10..15>;

/* Avoid: Using generic types */
/* age: Integer */
/* email: String */
```

### 5.9.2 Combine with Named Types

```walnut
/* Good: Named refined types */
UserId := Integer<1..>;
Username := String<3..20>;

/* Better readability than inline refinements */
user: [id: UserId, name: Username];
```

### 5.9.3 Leverage Type Inference

```walnut
/* Type system can infer refined types */
numbers = [1, 2, 3, 4, 5];
/* Type: [Integer[1], Integer[2], Integer[3], Integer[4], Integer[5]] */
/* Which is a subtype of Array<Integer<1..5>, 5..5> */

getFirst = ^ => Integer<1..5> :: numbers->item(0);
```

### 5.9.4 Use Subsets for Enumerations

```walnut
/* Good: Use string subsets for fixed values */
Status = String['pending', 'active', 'completed'];

/* Alternative: Use enumeration types */
StatusEnum := (Pending, Active, Completed);

/* Both provide compile-time checking */
```

## 5.10 Compile-Time vs Runtime Checking

**Compile-time checking:**
- Range constraints are checked when values are known at compile time
- Literal values are automatically refined to singleton types
- Type compatibility is verified during compilation

**Runtime checking:**
- Validators on user-defined types enforce constraints at runtime
- Cast operations may fail if values don't satisfy constraints
- Constructor functions can reject invalid values

**Example:**
```walnut
/* Compile-time check (succeeds) */
age = 42;  /* Type: Integer[42], can be assigned to Integer<0..150> */

/* Compile-time check (fails) */
/* age = 200; assigned to Integer<0..150> would fail */  /* Error */

/* Runtime check with Result type */
Age = Integer<0..150>;
Age(x: Integer) @ String ::
    ?when(x >= 0 && x <= 150) {
        x
    } ~ {
        @'Invalid age'
    };

result = Age(200);  /* Returns error at runtime */
```

## Summary

Type refinement in Walnut provides:

- **Range constraints** for Integer, Real, and String types
- **Length constraints** for String, Array, Map, and Set types
- **Value subsets** for Integer, Real, String, and Enumeration types
- **Advanced interval notation** for Real types
- **Compile-time validation** where possible
- **Runtime validation** through validators and constructors
- **Precise domain modeling** with business rule encoding
- **Type safety** through subtyping relationships

These features enable developers to build robust, type-safe applications where invalid states are often impossible to represent.
