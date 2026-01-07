# Type System

## Overview

Walnut features one of the most expressive type systems among programming languages, built on set-theoretical foundations with comprehensive subtyping support. The type system ensures compile-time safety while providing rich expressiveness for modeling domain logic.

## Type Hierarchy

### Any - The Top Type

`Any` is the top type in Walnut's type hierarchy. Every type is a subtype of `Any`. It represents any possible value in the language.

```walnut
x = 42;              /* Type: Integer (but can be assigned to Any) */
y = 'hello';         /* Type: String (but can be assigned to Any) */
z = [1, 2, 3];       /* Type: Tuple (but can be assigned to Any) */
```

Types like `Array` and `Map` without explicit type parameters are actually `Array<Any>` and `Map<String:Any>` (shorthand: `Map<Any>`).

### Nothing - The Bottom Type

`Nothing` is the bottom type in Walnut's type hierarchy. It is a subtype of every type and represents the absence of a value. `Nothing` is used internally for:

- Rest types in tuples: `[Integer]` is equivalent to `[Integer, ...Nothing]`
- Rest types in records: `[a: String]` is equivalent to `[a: String, ...Nothing]`
- Error types: `Error<T>` is equivalent to `Result<Nothing, T>`

### Subtyping Relationships

Walnut uses structural subtyping. A type `A` is a subtype of type `B` (written `A <: B`) if every value of type `A` can be safely used where a value of type `B` is expected.

**Key subtyping rules:**

1. **Reflexivity**: Every type is a subtype of itself
2. **Transitivity**: If `A <: B` and `B <: C`, then `A <: C`
3. **Top type**: Every type is a subtype of `Any`
4. **Bottom type**: `Nothing` is a subtype of every type
5. **Refinement**: A more specific type is a subtype of a less specific type
   - `Integer<1..10>` is a subtype of `Integer`
   - `String<5..10>` is a subtype of `String`
6. **Collection refinement**: Collections with refined element types are subtypes
   - `Array<Integer>` is a subtype of `Array<Real>` (covariant)
   - `[Integer, Real]` is a subtype of `Array<Real, ..5>`

## Primitive Types

### Integer

Represents whole numbers with optional range constraints.

**Syntax:**
- `Integer` - all integers
- `Integer<min..max>` - integers in a closed range
- `Integer<min..>` - integers from min onwards
- `Integer<..max>` - integers up to max

**Examples:**
```walnut
/* These are type annotations for function parameters/return types */
/* age: Integer<0..150> */
/* positiveNumber: Integer<1..> */
/* negativeOrZero: Integer<..0> */
/* smallNumber: Integer<-10..10> */

x = 42;                   /* Type: Integer[42] */
y = 50;  /* Type: Integer[50] (can be assigned to Integer<1..100>) */
```

**Notes:**
- The literal `42` has type `Integer[42]` (singleton subset)
- Integer ranges use inclusive endpoints
- Range constraints are checked at compile time when possible

**Precision:**
- Integers have **no inherent bit-width limitations** (no 32-bit or 64-bit constraints)
- Internally uses **BcMath** for arbitrary-precision arithmetic
- Can represent arbitrarily large integers (limited only by available memory)
- Range constraints (e.g., `Integer<min..max>`) can be used to explicitly limit the value range when needed
- When working with JSON values, extremely large numbers may be problematic due to JSON parser limitations

### Real

Represents real numbers (floating-point) with optional range constraints and support for open/closed intervals.

**Syntax:**
- `Real` - all real numbers
- `Real<min..max>` - reals in a closed range `[min, max]`
- `Real<(min..max)>` - reals in an open range `(min, max)`
- `Real<[min..max)>` - reals in a half-open range `[min, max)`
- `Real<(min..max]>` - reals in a half-open range `(min, max]`
- `Real<min..>` - reals from min onwards
- `Real<..max>` - reals up to max

**Advanced range syntax:**
Multiple intervals can be combined using commas:
```walnut
/* Union of multiple intervals */
Real<(..-2], [1..5.29), (6.43..)>
/* Represents: (-∞, -2] ∪ [1, 5.29) ∪ (6.43, +∞) */
```

**Examples:**
```walnut
/* Type annotations for function parameters/returns */
/* temperature: Real<-273.15..>  -- Absolute zero and above */
/* probability: Real<[0..1]>  -- Closed interval [0, 1] */
/* nonZero: Real<(..-0), (0..)>  -- All reals except zero */
/* percentage: Real<0..100> */

pi = 3.14159;                    /* Type: Real[3.14159] */
```

**Notes:**
- The literal `3.14` has type `Real[3.14]`
- Parentheses `()` indicate open (exclusive) endpoints
- Brackets `[]` indicate closed (inclusive) endpoints
- Default notation `min..max` uses closed intervals

**Precision:**
- Real numbers have **no inherent bit-width limitations** (no 32-bit or 64-bit float/double constraints)
- Internally uses **BcMath** for arbitrary-precision decimal arithmetic
- Can represent numbers with arbitrary precision (limited only by available memory)
- Range constraints (e.g., `Real<min..max>`) can be used to explicitly limit the value range when needed
- When working with JSON values, extremely large numbers or very high precision may be problematic due to JSON parser limitations

### String

Represents sequences of characters with optional length constraints.

**Syntax:**
- `String` - strings of any length
- `String<min..max>` - strings with length in range
- `String<min..>` - strings with minimum length
- `String<..max>` - strings with maximum length

**Examples:**
```walnut
/* Type annotations for function parameters/returns */
/* name: String<1..100>  -- Non-empty, up to 100 chars */
/* password: String<8..>  -- At least 8 characters */
/* shortCode: String<..10>  -- Up to 10 characters */

greeting = 'Hello';        /* Type: String['Hello'] */
```

**String literals:**
```walnut
'simple string'
'string with escaped quote: \' and backslash: \\ and newline: \n'
```

### Boolean

A special enumeration type with two values: `true` and `false`.

**Types:**
- `Boolean` - the enumeration type containing both values
- `True` - enumeration subset containing only `true`
- `False` - enumeration subset containing only `false`

**Examples:**
```walnut
flag = true;
isValid = true;      /* Type: True */
isFalse = false;    /* Type: False */

x = true;                  /* Type: True */
y = false;                 /* Type: False */
```

### Null

A special atom type with a single value `null`.

**Type:** `Null`

**Value:** `null`

**Examples:**
```walnut
empty = null;
optional = null;  /* Type: Null (can be assigned to Integer|Null) */

x = null;                  /* Type: Null */
```

## Subset Types

Subset types represent finite sets of specific values from a base type.

### IntegerSubset

Represents a finite set of integer values.

**Syntax:** `Integer[value1, value2, ...]`

**Examples:**
```walnut
DiceRoll = Integer[1, 2, 3, 4, 5, 6];
PrimeDigit = Integer[2, 3, 5, 7];
SingleValue = Integer[42];

roll = 5;  /* Type: Integer[5], can be assigned to DiceRoll */
prime = 7;  /* Type: Integer[7], can be assigned to PrimeDigit */
```

**Notes:**
- A literal like `42` has type `Integer[42]`
- Subset types are subtypes of their base type
- `Integer[5]` is a subtype of `Integer<1..10>`

### RealSubset

Represents a finite set of real number values.

**Syntax:** `Real[value1, value2, ...]`

**Examples:**
```walnut
MathConstants = Real[3.14159, 2.71828];
Benchmarks = Real[0.0, 0.5, 1.0];

pi = 3.14159;  /* Type: Real[3.14159] */
```

### StringSubset

Represents a finite set of string values.

**Syntax:** `String['value1', 'value2', ...]`

**Examples:**
```walnut
Direction = String['north', 'south', 'east', 'west'];
Greeting = String['hello', 'hi', 'hey'];

dir = 'north';  /* Type: String['north'], can be assigned to Direction */
msg = 'hello';  /* Type: String['hello'], can be assigned to Greeting */
```

**Notes:**
- A literal like `'hello'` has type `String['hello']`
- String subsets are useful for representing sets of allowed values

### EnumerationSubset

Subsets of user-defined enumeration types.

**Examples:**
```walnut
/* Define an enumeration */
Suit := (Clubs, Diamonds, Hearts, Spades);

/* Define a subset */
RedSuit = Suit[Diamonds, Hearts];
BlackSuit = Suit[Clubs, Spades];

/* Usage */
card = Suit.Hearts;  /* Type: Suit[Hearts], can be assigned to RedSuit */
```

## Collection Types

### Array

Ordered, indexed collections with optional element type and length constraints.

**Syntax:**
- `Array` - equivalent to `Array<Any>`
- `Array<T>` - array of elements of type T
- `Array<min..max>` - array with length constraint
- `Array<T, min..max>` - array of type T with length constraint

**Examples:**
```walnut
/* Type annotations for function parameters/returns */
/* numbers: Array<Integer> */
/* limitedList: Array<1..10> */
/* scores: Array<Integer, 5..> */
/* matrix: Array<Array<Real>> */

values = [1, 2, 3, 4, 5];  /* Type: [Integer[1], Integer[2], Integer[3], Integer[4], Integer[5]] */
```

**Subtyping:**
- `[Integer, Real]` is a subtype of `Array<Real, ..5>`
- Arrays are covariant in their element type

### Map

Unordered key-value collections with typed keys (must be string subtypes) and homogeneous values.

**Syntax:**
- `Map` - equivalent to `Map<String:Any>` (default string keys, any values)
- `Map<T>` - equivalent to `Map<String:T>` (string keys, values of type T)
- `Map<K:T>` - map with key type K (must be subtype of String) and value type T
- `Map<min..max>` - map with length constraint (string keys, any values)
- `Map<T, min..max>` - map with value type T and length constraint (string keys)
- `Map<K:T, min..max>` - map with key type K, value type T, and length constraint

**Key type constraints:**
- Key type `K` must be a subtype of `String`
- Valid key types: `String`, `String<min..max>`, `String[val1, val2, ...]`, or aliases/unions of these
- Invalid: `Map<Integer:String>` (Integer is not a subtype of String)

**Examples:**
```walnut
/* Type annotations for function parameters/returns */
/* userData: Map<String> or Map<String:String> */
/* config: Map<String:Integer|String> */
/* limitedMap: Map<String:Any, 1..10> */
/* statusMap: Map<String['active','pending']:Integer> */

person = [name: 'Alice', city: 'NYC'];  /* Type: [name: String['Alice'], city: String['NYC']] */

/* Maps with refined key types */
ConfigKeys = String['host', 'port', 'debug'];
config = [host: 'localhost', port: 5432, debug: true];
/* Type: Map<ConfigKeys:String|Integer|Boolean, 3..3> */
```

**Subtyping:**
- `[a: Integer, b: Real, z: Boolean]` is a subtype of `Map<String:Real, 2..>`
- Maps are covariant in their value type (like arrays)
- Key type refinements are included in subtype checking

### Set

Unordered collections of unique values with optional element type and length constraints.

**Syntax:**
- `Set` - equivalent to `Set<Any>`
- `Set<T>` - set of elements of type T
- `Set<min..max>` - set with length constraint
- `Set<T, min..max>` - set of type T with length constraint

**Examples:**
```walnut
/* Type annotations for function parameters/returns */
/* uniqueIds: Set<Integer> */
/* tags: Set<String, 1..10> */
/* anySet: Set */

primes = [2; 3; 5; 7; 11];  /* Type: Set of specific integers */
```

**Notes:**
- Sets automatically deduplicate values
- `[1; 'hello'; null]` has type `Set<Integer[1]|String['hello']|Null, 3..3>`

### Tuple

Fixed-length ordered collections with per-position types and optional rest type.

**Syntax:**
- `[]` - empty tuple
- `[T1, T2, ...]` - tuple with specific types for each position
- `[T1, T2, ...Rest]` - tuple with rest type (variable-length tail)

**Examples:**
```walnut
empty = [];
pair = [42, 'hello'];  /* Type: [Integer[42], String['hello']] */
coords = [1.0, 2.0, 3.0];  /* Type: [Real[1.0], Real[2.0], Real[3.0]] */

/* Variable-length tuple */
items = ['first', 1, 2, 3];
/* Type: [String['first'], Integer[1], Integer[2], Integer[3]] */

any = [null, [], 3, 'extra', true];
/* Type: [Null, [], Integer[3], String['extra'], True] */
```

**Notes:**
- `[Integer]` is equivalent to `[Integer, ...Nothing]`
- Tuples are covariant in their element types
- A tuple can be used as an Array if element types are compatible

### Record

Collections with named keys, optional keys, and optional rest type.

**Syntax:**
- `[:]` - empty record
- `[key1: T1, key2: T2, ...]` - record with specific keys and types
- `[key: ?T]` - optional key (can be missing)
- `[key: T, ...Rest]` - record with rest type for additional keys

**Examples:**
```walnut
empty = [:];
person = [name: 'Alice', age: 30];  /* Type: [name: String['Alice'], age: Integer[30]] */

/* Optional keys */
user = [name: 'Bob'];  /* Type: [name: String['Bob']] */

/* Rest type */
config = [host: 'localhost', port: 8080, path: '/api', protocol: 'http'];
/* Type: [host: String['localhost'], port: Integer[8080], path: String['/api'], protocol: String['http']] */

/* Rest type with Any */
flexible = [id: 1, data: 'misc', count: 5];
```

**Optional keys:**
- `?T` is shorthand for `OptionalKey<T>`
- Can only be used in record type definitions
- Optional keys may be missing from record values

**Subtyping:**
- `[a: Integer, b: Real, z: Boolean]` is a subtype of `[z: Boolean, ... Real]`
- Records are structural subtypes based on key compatibility

## Function Types

Functions are first-class values with types describing their parameter and return types.

**Syntax:** `^ParamType => ReturnType`

**Examples:**
```walnut
/* Type annotations for function parameters/returns */
/* simpleFunc: ^Integer => String */
/* identity: ^Real => Real */
/* process: ^[x: Integer, y: String] => Boolean */
/* transform: ^Array<Real> => Result<Integer, String> */

/* Function values */
add = ^x: Integer, y: Integer => Integer :: x + y;
greet = ^name: String => String :: 'Hello, ' + name;

/* With dependency injection */
processor = ^data: String => Integer %% [~Database] ::
    %database->query(data)->length;
```

**Type variance:**
- Function parameters are **contravariant**
- Function return types are **covariant**
- If `A <: B`, then `^B => R <: ^A => R` (contravariance)
- If `R1 <: R2`, then `^P => R1 <: ^P => R2` (covariance)

**Notes:**
- Functions can capture variables from their environment
- Dependency parameters (marked with `%%`) enable compile-time dependency injection
- Functions are immutable and referentially transparent

## Special Types

### Mutable&lt;T&gt;

Represents a mutable reference to a value of type `T`. This is the only way to have mutable state in Walnut.

**Syntax:** `Mutable<T>` or `Mutable`

**Examples:**
```walnut
/* Type annotations for function parameters/returns */
/* counter: Mutable<Integer<0..100>> */
/* state: Mutable<[status: String, count: Integer]> */
/* anyMutable: Mutable */

/* Creating mutable values */
x = mutable{Integer<0..100>, 5};
y = mutable{Real, 3.14};
z = mutable{Any, 'hello'};

/* Type of mutable value */
w = mutable{Real, 3.14};  /* Type: Mutable<Real> */
```

**Important characteristics:**
- The inner type `T` is **invariant** (not covariant)
- `Mutable<Integer>` is NOT a subtype of `Mutable<Real>`
- This prevents type safety violations
- Use methods like `SET`, `ADD`, `PUSH` to modify mutable values

### Result&lt;OkType, ErrorType&gt;

Represents a computation that can either succeed with a value of `OkType` or fail with an error of `ErrorType`.

**Syntax:** `Result<OkType, ErrorType>`

**Examples:**
```walnut
/* Type annotations for function parameters/returns */
/* outcome: Result<Integer, String> */
/* operation: Result<[data: String], ValidationError> */

/* Values */
success = 42;  /* Type: Integer[42], can be Result<Integer, String> */
failure = @'Not found';  /* Type: Error<String['Not found']> */

/* Function returning result */
divide = ^numerator: Real, denominator: Real => Result<Real, String> ::
    ?when(denominator == 0) {
        @'Division by zero'
    } ~ {
        numerator / denominator
    };
```

**Notes:**
- Informally, `Result<T, E>` corresponds to `T|Error<E>`
- Error values can be created with `@errorValue`
- The `?` operator unwraps successful results or returns early on error

### Error&lt;T&gt;

A shorthand for representing only error values.

**Syntax:** `Error<T>` (equivalent to `Result<Nothing, T>`)

**Examples:**
```walnut
/* Type annotations for function parameters/returns */
/* fileError: Error<String> */
/* validationError: Error<ValidationFailure> */

/* Creating error values */
notFound = @'File not found';  /* Type: Error<String['File not found']> */
invalid = @'Invalid input';  /* Type: Error<String['Invalid input']> */

/* Type inference */
err = @'Something went wrong';  /* Type: Error<String['Something went wrong']> */
```

### ExternalError

A special sealed type used to wrap errors from external sources (I/O, network, database, etc.).

**Definition (in core library):**
```walnut
ExternalError := $[
    errorType: String,
    originalError: Any,
    errorMessage: String
];
```

**Examples:**
```walnut
/* Type annotation for function return */
/* readResult: Result<String, ExternalError> */

/* Creating external errors (typically done by runtime) */
dbError = @ExternalError[
    errorType: 'DatabaseError',
    originalError: rawError,
    errorMessage: 'Connection failed'
];

/* Converting to external error */
result *> 'Custom error message';
```

**Notes:**
- Used to represent errors from impure operations
- Cannot be created directly in pure code
- Converted automatically by impure operations

### Impure&lt;T&gt;

A shorthand for operations that may fail with external errors.

**Syntax:** `Impure<T>` (equivalent to `Result<T, ExternalError>`)

**Shorthand notation:** `*T` is equivalent to `Impure<T>`

**Examples:**
```walnut
/* Type annotations for function returns */
/* fileContent: Impure<String> */
/* queryResult: *Array<User> */

/* Equivalent to */
/* fileContent: Result<String, ExternalError> */
/* queryResult: Result<Array<User>, ExternalError> */

/* Nested result type annotation */
/* processResult: *Result<Integer, String> */
/* Equivalent to: Result<Integer, ExternalError|String> */
```

**Notes:**
- Represents side effects that may fail
- Commonly used for I/O operations
- The `*?` operator handles external errors

### Shape&lt;T&gt;

Represents values that can be converted to or used as type `T`.

**Syntax:** `Shape<T>` or `{T}` (short syntax)

**Examples:**
```walnut
/* Type annotations for function parameters */
/* numericShape: Shape<Integer> */
/* coordShape: Shape<[x: Real, y: Real, ...]> */

/* Short syntax */
/* numericShape: {Integer} */
/* coordShape: {x: Real, y: Real, ...} */

/* Usage */
getName = ^obj: {String} => String ::
    'The name is: ' + obj->shape(`String);

/* Works with compatible types */
Product := $[id: Integer, name: String, price: Real];
p = Product[1, 'Tomato', 2.34];
getName(p);  /* Returns 'The name is: Tomato' */
```

**Shape compatibility:**

A value `v` of type `V` is compatible with `Shape<T>` if:
1. `V` is a subtype of `T`, OR
2. `V` is a data type based on `T`, OR
3. There exists a cast method `V ==> T` with no error return type

**Notes:**
- Provides duck-typing capabilities in a strongly-typed system
- Enables polymorphic behavior across different types
- The `->shape(typeValue)` method extracts the shaped value

## Type Operators

### Union (T1|T2)

Represents a value that can be either of type `T1` or type `T2` (or both).

**Syntax:** `T1|T2|T3|...`

**Examples:**
```walnut
/* Type annotations for function parameters/returns */
/* optionalInt: Integer|Null */
/* numberType: Integer|Real */
/* mixed: String|Integer|Boolean */
/* result: Integer|Error<String> */

/* Values */
x = 42;  /* Type: Integer[42], can be assigned to Integer|String */
y = 'hello';  /* Type: String['hello'], can be assigned to Integer|String */

/* Type inference */
val = ?when(condition) { 42 } ~ { 'default' };
/* Type: Integer[42]|String['default'] */
```

**Properties:**
- Commutative: `A|B` is the same as `B|A`
- Associative: `A|(B|C)` is the same as `(A|B)|C`
- Idempotent: `A|A` is the same as `A`
- A union type is a supertype of all its constituent types

### Intersection (T1&T2)

Represents a value that is of both type `T1` AND type `T2` simultaneously.

**Syntax:** `T1&T2&T3&...`

**Examples:**
```walnut
/* Type annotations */
/* point: [x: Real, ...] & [y: Real, ...] */
/* A record that has both x and y keys (and possibly more) */

/* extended: [a: String, ...] & [b: Integer, ...String] */
/* Has both 'a' (String) and 'b' (Integer) keys, rest are Strings */

/* specific: Array<Integer> & Array<Real> & Array<String> */
/* Empty array (no value can be all three types simultaneously) */

/* compatible: [Integer, String] & Array<Integer|String, 2..2> */
/* A 2-element tuple with Integer and String */
```

**Properties:**
- Commutative: `A&B` is the same as `B&A`
- Associative: `A&(B&C)` is the same as `(A&B)&C`
- Idempotent: `A&A` is the same as `A`
- An intersection type is a subtype of all its constituent types
- Can result in empty types if constituents are incompatible

**Use cases:**
- Combining multiple record types
- Expressing complex structural requirements
- Type narrowing in conditional expressions

## Proxy Types

Proxy types allow forward references to types that haven't been defined yet. This is necessary for recursive and mutually recursive type definitions.

**Syntax:** `\TypeName`

**Examples:**
```walnut
/* Recursive type definition */
NodeElement = [left: \Node, value: Integer, right: \Node];
Node = NodeElement|Null;

/* Mutually recursive types */
TreeNode = [value: Integer, children: Array<\Tree>];
Tree = TreeNode|Null;

/* In collections - type annotation */
/* linkedList: Array<\ListNode> */
ListNode = [value: Any, next: \ListNode|Null];
```

**Notes:**
- Only needed when a type references itself or types not yet defined
- The backslash `\` indicates a proxy (forward reference)
- Resolved at compile time once all types are defined
- Cannot be used in all contexts (mainly for structural types)

## Meta-Types

Meta-types are special types that can only be used within `Type<...>` type definitions. They allow restricting type parameters to specific categories of types.

**Available meta-types:**

- `Function` - any function type
- `Tuple` - any tuple type
- `Record` - any record type
- `Union` - any union type
- `Intersection` - any intersection type
- `Atom` - any atom type (including Null)
- `Enumeration` - any enumeration type
- `EnumerationSubset` - any enumeration subset type
- `IntegerSubset` - any integer subset type
- `RealSubset` - any real subset type
- `StringSubset` - any string subset type
- `MutableValue` - any mutable type
- `Alias` - any alias type
- `Data` - any data type
- `Open` - any open type
- `Sealed` - any sealed type
- `Named` - any named type (Atom, Enumeration, Alias, Data, Open, or Sealed)

**Examples:**
```walnut
/* From demo-all.nut */
AllTypes = [
    anyType: Type,
    anyReal: Type<Real>,

    anyIntegerSubset: Type<IntegerSubset>,
    anyRealSubset: Type<RealSubset>,
    anyStringSubset: Type<StringSubset>,
    anyFunction: Type<Function>,
    anyAtom: Type<Atom>,
    anyEnumeration: Type<Enumeration>,
    anyEnumerationSubset: Type<EnumerationSubset>,
    anyOpen: Type<Open>,
    anySealed: Type<Sealed>,
    anyData: Type<Data>,
    anyNamed: Type<Named>,
    anyAlias: Type<Alias>,
    anyTuple: Type<Tuple>,
    anyRecord: Type<Record>,
    anyMutable: Type<MutableValue>,
    anyIntersection: Type<Intersection>,
    anyUnion: Type<Union>
];

/* Usage in function signatures */
processEnum = ^enumType: Type<Enumeration> => String ::
    enumType->name;

transformFunc = ^fn: Type<Function> => Type<Function> ::
    /* Manipulate function type */
    fn;
```

**Notes:**
- Meta-types enable type-level programming
- Used for reflection and metaprogramming
- Cannot be used outside of `Type<...>` contexts
- Allow precise constraints on type parameters

## Type Values

Types themselves can be used as values in Walnut, enabling reflection and metaprogramming.

**Syntax:** `` `TypeExpression ``

**Examples:**
```walnut
/* Type values */
intType = `Integer;  /* Type: Type<Integer> */
stringType = `String;  /* Type: Type<String> */
arrayType = `Array<Integer, ..5>;
recordType = `[a: Integer, b: String];

/* Complex types */
functionType = `^Integer => String;
unionType = `Integer|String;
intersectionType = `[a: String, ...] & [b: Integer, ...String];

/* User-defined types */
MyAtom := ();
atomType = `MyAtom;
```

**Type of type values:**
- `` `Integer `` has type `Type<Integer>`
- `` `String['hello'] `` has type `Type<StringSubset>`
- `` `^Integer => String `` has type `Type<Function>`

**Usage:**
```walnut
/* Runtime type checking */
checkType = ^value: Any, expectedType: Type => Boolean ::
    value->type->isSubtypeOf(expectedType);

/* Dynamic casting */
castTo = ^value: Any, targetType: Type<T> => Result<T, String> ::
    ?when(value->type->isSubtypeOf(targetType)) {
        value->as(targetType)
    } ~ {
        @'Type mismatch'
    };
```

## Type Variance and Contravariance

### Covariance

A type constructor is covariant if the subtyping relationship is preserved.

**Examples:**
- **Arrays**: `Array<A>` is a subtype of `Array<B>` if `A <: B`
- **Return types**: `^P => A` is a subtype of `^P => B` if `A <: B`
- **Tuples**: `[A, B]` is a subtype of `[C, D]` if `A <: C` and `B <: D`

```walnut
/* Covariant example */
intArray = [1, 2, 3];
realArray = intArray;  /* OK: Array of Integers can be used as Array<Real> */
```

### Contravariance

A type constructor is contravariant if the subtyping relationship is reversed.

**Examples:**
- **Function parameters**: `^B => R` is a subtype of `^A => R` if `A <: B`

```walnut
/* Contravariant example */
processReal = ^x: Real => String :: x->asString;
processInt = processReal;  /* Type: ^Real => String, compatible with ^Integer => String */
/* OK: ^Real => String <: ^Integer => String */
/* Because Integer <: Real (contravariance on parameter) */
```

**Why contravariance for parameters?**

If a function can handle any `Real`, it can certainly handle any `Integer` (since integers are reals). Therefore, a function expecting a more general type can be safely used where a function expecting a more specific type is needed.

### Invariance

A type constructor is invariant if no subtyping relationship is preserved.

**Example:**
- **Mutable types**: `Mutable<T>` is invariant in `T`

```walnut
/* Invariance example */
mutInt = mutable{Integer, 5};
mutReal = mutInt;  /* ERROR: Mutable is invariant */

/* Why? If this were allowed: */
mutReal->SET(3.14);  /* Would write a Real into an Integer mutable */
```

**Why invariance for Mutable?**

Mutable values can be both read from and written to:
- Reading requires covariance (safe to read Integer as Real)
- Writing requires contravariance (safe to write Real to Real, not to Integer)
- These requirements conflict, so `Mutable<T>` must be invariant

## Practical Examples

### Modeling Domain Logic

```walnut
/* Product catalog types */
ProductId := Integer<1..>;
Price := Real<0..>;
ProductName := String<1..100>;

Product := $[
    id: ProductId,
    name: ProductName,
    price: Price
];

/* Function with refined types */
calculateDiscount = ^price: Price => Price ::
    ?when(price > 100) {
        price * 0.1
    } ~ { 0 };

/* Result type for operations that can fail */
findProduct = ^id: ProductId => Result<Product, String> ::
    /* ... lookup logic */
    @'Product not found';
```

### Working with Collections

```walnut
/* Type annotations for function parameters/returns */
/* scores: Array<Integer<0..100>, 1..> */
/* usernames: Set<String<3..20>> */
/* config: Map<String|Integer> */

/* Tuples for fixed-size data */
Point2D = [Real, Real];
Point3D = [Real, Real, Real];

/* Records with optional fields */
User = [
    id: Integer<1..>,
    name: String<1..100>,
    email: String<5..>,
    nickname: ?String<1..50>,
    ... String
];
```

### Advanced Type Combinations

```walnut
/* Union for flexible return types */
QueryResult = Array<Record>|Error<String>;

/* Intersection for complex constraints */
ValidatedUser = [id: Integer, ...] & [email: String, ...] & [name: String, ...];

/* Nested generic types */
Matrix = Array<Array<Real>>;
NestedMap = Map<Map<String>>;

/* Function composition types */
Transform = ^Array<Integer> => Array<String>;
Validator = ^String => Result<Boolean, ValidationError>;
```

## Type System Best Practices

1. **Use refined types** for domain constraints
   ```walnut
   Age := Integer<0..150>;
   Email := String<5..254>;
   ```

2. **Prefer explicit types** for public APIs
   ```walnut
   /* Good */
   processUser = ^user: User => Result<Response, Error> :: ...

   /* Avoid */
   processUser = ^user => ...
   ```

3. **Use Result types** for operations that can fail
   ```walnut
   divide = ^x: Real, y: Real => Result<Real, String> :: ...
   ```

4. **Leverage union types** for alternatives
   ```walnut
   Status = String['pending', 'active', 'completed'];
   ```

5. **Use Shape types** for structural polymorphism
   ```walnut
   processEntity = ^entity: {id: Integer, name: String} => String :: ...
   ```

6. **Avoid overusing Any** - prefer specific types when possible
   ```walnut
   /* Good */
   items: Array<Product>

   /* Avoid */
   items: Array  /* Same as Array<Any> */
   ```

## Summary

The Walnut type system provides:

- **Expressive power**: Rich type constructs for precise modeling
- **Safety**: Compile-time checking prevents runtime type errors
- **Flexibility**: Union, intersection, and shape types for complex scenarios
- **Refinement**: Range and length constraints for domain modeling
- **Composition**: Types can be combined in sophisticated ways
- **First-class types**: Types as values enable metaprogramming

This combination makes Walnut suitable for building robust, maintainable applications with strong compile-time guarantees while maintaining expressiveness and flexibility.
