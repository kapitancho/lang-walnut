# 3. Values and Literals

This section describes all value forms and literal syntax in Walnut, including type inference rules and constant expressions.

## 3.1 Constant Values

Constant values are primitive values that can be written directly in source code.

### 3.1.1 Integer Literals

Integer literals represent whole numbers.

**Syntax:**
```
integer-literal: [-]?[0-9]+
```

**Examples:**
```walnut
42
0
-17
1000000
```

**Type Inference:**
Integer literals have a refined type. The value `42` has the type `Integer[42]` (a subset type containing only that value), which is a subtype of the range type `Integer<42..42>`, which is itself a subtype of `Integer`.

**Examples with types:**
```walnut
5          /* Type: Integer[5] */
-100       /* Type: Integer[-100] */
0          /* Type: Integer[0] */
```

### 3.1.2 Real Literals

Real literals represent floating-point numbers.

**Syntax:**
```
real-literal: [-]?[0-9]+\.[0-9]+
```

**Examples:**
```walnut
3.14
-2.71
0.5
100.0
-7.3
```

**Type Inference:**
Real literals have refined types. The value `3.14` has type `Real[3.14]`, which is a subtype of `Real<3.14..3.14>`, which is a subtype of `Real`.

**Examples with types:**
```walnut
3.14       /* Type: Real[3.14] */
-2.71      /* Type: Real[-2.71] */
0.0        /* Type: Real[0.0] */
```

### 3.1.3 String Literals

String literals are enclosed in single quotes.

**Syntax:**
```
string-literal: '([^'\\]|\\['\\\ntr])*'
```

**Examples:**
```walnut
'hello'
'Hello, World!'
''                      /* Empty string */
'It\'s a beautiful day' /* Escaped quote */
```

**Escape Sequences:**
- `` \` `` - Single quote
- ` \\ ` - Backslash
- ` \n ` - Newline

**Examples with escape sequences:**
```walnut
'Line 1\nLine 2'        /* Multi-line text */
'Tab\there'             /* Tab character */
'Backslash: \\'         /* Backslash character */
'Quote: \`'             /* Single quote */
```

**Type Inference:**
String literals have refined types based on both their value and length. The string `'hello'` has:
- Subset type: `String['hello']` (contains only that exact string)
- Length-based type: `String<5..5>` (string of exactly length 5)
- Base type: `String`

**Examples with types:**
```walnut
'hello'    /* Type: String['hello'], also String<5..5> */
''         /* Type: String[''], also String<0..0> */
'a'        /* Type: String['a'], also String<1..1> */
```

### 3.1.4 Boolean Literals

Boolean values represent truth values.

**Syntax:**
```walnut
true
false
```

**Type Inference:**
- `true` has type `True` (an enumeration subset containing only `true`)
- `false` has type `False` (an enumeration subset containing only `false`)
- Both are subtypes of `Boolean` (the enumeration type `(true, false)`)

**Examples:**
```walnut
true       /* Type: True */
false      /* Type: False */
```

### 3.1.5 Null Literal

The null value represents the absence of a value.

**Syntax:**
```walnut
null
```

**Type Inference:**
`null` is the only value of type `Null`, which is an atom type.

**Example:**
```walnut
null       /* Type: Null */
```

## 3.2 Atom Values

Atoms are singleton types with a single value. The atom type itself is its value.

**Declaration Syntax:**
```walnut
AtomName := ();
```

**Examples:**
```walnut
/* Atom declarations */
MyAtom := ();
NotFound := ();
InvalidDate := ();
HttpNotFoundHandler := ();

/* Atom values */
MyAtom         /* The atom value itself */
NotFound       /* Type and value are the same */
```

**Type Inference:**
An atom value has the type of the atom itself. For example, `MyAtom` has type `MyAtom`.

**Common uses:**
```walnut
/* As error types */
ItemNotFound := ();
InvalidInput := ();

/* As markers/sentinels */
EmptyRequestBody := ();
RoutePatternDoesNotMatch := ();

/* As service identifiers */
Clock := ();
TemplateRenderer := ();
```

## 3.3 Enumeration Values

Enumerations are types with a fixed set of named values.

**Declaration Syntax:**
```walnut
EnumName := (Value1, Value2, Value3);
```

**Value Access Syntax:**
```walnut
EnumName.Value1
EnumName.Value2
```

**Examples:**
```walnut
/* Enumeration declarations */
Boolean := (true, false);
Suit := (Hearts, Diamonds, Clubs, Spades);
HttpRequestMethod := (connect, delete, get, head, options, patch, post, put, trace);
SqlOrderByDirection := (Asc, Desc);

/* Enumeration values */
Suit.Spades            /* Type: Suit[Spades] (enumeration subset) */
Suit.Hearts            /* Type: Suit[Hearts] */
HttpRequestMethod.get  /* Type: HttpRequestMethod[get] */
```

**Type Inference:**
An enumeration value has an enumeration subset type. For example:
- `Suit.Hearts` has type `Suit[Hearts]`
- `Boolean.true` has type `True` (which is shorthand for `Boolean[true]`)

**Enumeration subsets:**
```walnut
Suit := (Hearts, Diamonds, Clubs, Spades);

/* A function that accepts only red suits */
myFn = ^Suit[Hearts, Diamonds] => String :: #->asString;

myFn(Suit.Hearts)      /* OK */
myFn(Suit.Spades)      /* Type error */
```

## 3.4 Tuple Values

Tuples are ordered, fixed-length collections with heterogeneous types.

**Syntax:**
```walnut
[]                     /* Empty tuple */
[value1]               /* Single-element tuple */
[value1, value2, ...]  /* Multi-element tuple */
```

**Examples:**
```walnut
[]                     /* Empty tuple */
[1, 2, 3]              /* Three integers */
[1, 'hello']           /* Mixed types */
[1, 'hello', true]     /* Three different types */
[3, true, 'Hello', 'World']  /* Four elements */
```

**Type Inference:**
Tuples have tuple types that capture the type of each element:

```walnut
[]                     /* Type: [] */
[1, 2, 3]              /* Type: [Integer[1], Integer[2], Integer[3]] */
[1, 'hello']           /* Type: [Integer[1], String['hello']] */
```

**Tuple types:**
```walnut
/* Closed tuple type - exactly 3 elements */
Tup1 = [Integer, String, Boolean];

/* Open tuple type - at least 2 elements, rest are Real */
Tup2 = [Integer, String, ... Real];

/* Using tuple values */
x = [1, 'hello', true];           /* Type: [Integer[1], String['hello'], True] */
y = [1, 'hello', 3.14, 2.71];     /* Type: [Integer[1], String['hello'], Real[3.14], Real[2.71]] */
```

**Element access:**
```walnut
tuple = [1, 'hello', true];
tuple.0        /* 1 (first element) */
tuple.1        /* 'hello' (second element) */
tuple.2        /* true (third element) */
```

**Tuple as Array:**
A tuple is a subtype of Array:
```walnut
[1, 2, 3]              /* Can be used as Array<Integer, 3..3> */
[1, 'hi', true]        /* Can be used as Array<Integer|String|Boolean, 3..3> */
```

## 3.5 Record Values

Records are unordered collections of key-value pairs with named fields.

**Syntax:**
```walnut
[:]                          /* Empty record */
[key: value]                 /* Single field */
[key1: value1, key2: value2] /* Multiple fields */
```

**Examples:**
```walnut
[:]                          /* Empty record */
[a: 1, b: 2]                 /* Two fields */
[name: 'Alice', age: 30]     /* Named fields */
[a: 1, b: true, c: 'Hello', d: 'World']  /* Mixed types */
```

**Type Inference:**
Records have record types that capture the type of each field:

```walnut
[:]                          /* Type: [:] */
[a: 1, b: 2]                 /* Type: [a: Integer[1], b: Integer[2]] */
[name: 'Alice', age: 30]     /* Type: [name: String['Alice'], age: Integer[30]] */
```

**Record types:**
```walnut
/* Closed record type - exactly these fields */
Rec1 = [a: Integer, b: String, c: Boolean];

/* Record with optional fields */
Rec2 = [a: Integer, b: OptionalKey<String>, c: OptionalKey];

/* Open record type - at least field 'a', rest are Real */
Rec3 = [a: Integer, ... Real];

/* Using record values */
w = [a: 3, b: true, c: 'Hello', d: 'World'];
y = [a: 3, b: true, c: 'Hello'];
z = [a: 3];
```

**Field access:**
```walnut
record = [name: 'Alice', age: 30];
record.name        /* 'Alice' */
record.age         /* 30 */
```

**Dynamic field access:**
```walnut
record = [a: 1, b: 2, c: 3];
record->item('b')  /* Result: 2 */
```

**Record as Map:**
A record is a subtype of Map:
```walnut
[a: 1, b: 2]               /* Can be used as Map<String:Integer, 2..2> */
[a: 1, b: 'hi']            /* Can be used as Map<String:Integer|String, 2..2> */
```

## 3.6 Set Values

Sets are unordered collections of unique values.

**Syntax:**
```walnut
[;]                        /* Empty set */
[value;]                   /* Single-element set */
[value1; value2; value3]   /* Multi-element set */
```

**Examples:**
```walnut
[;]                        /* Empty set */
[1;]                       /* Single integer */
[1; 2; 3]                  /* Three integers */
[1; 4; 'hello']            /* Mixed types */
[7; 3; 3]                  /* Duplicates removed -> [7; 3] */
```

**Type Inference:**
Sets have set types:

```walnut
[;]                        /* Type: Set<Nothing, 0..0> */
[1;]                       /* Type: Set<Integer[1], 1..1> */
[1; 2; 3]                  /* Type: Set<Integer[1, 2, 3], 3..3> */
[1; 4; 'hello']            /* Type: Set<Integer|String, 3..3> */
```

**Set types:**
```walnut
/* Set of integers */
MySet = Set<Integer>;

/* Set of reals with max 5 elements */
MySet2 = Set<Real, ..5>;

/* Set with length range */
MySet3 = Set<String, 1..10>;
```

**Set operations:**
```walnut
[1; 2; 3] + [2; 4]         /* Union: [1; 2; 3; 4] */
[1; 2; 3] - [2; 4]         /* Difference: [1; 3] */
[1; 2; 3] & [2; 4]         /* Intersection: [2] */
[1; 2; 3] ^ [2; 4]         /* Symmetric difference: [1; 3; 4] */
```

**Set methods:**
```walnut
set = [1; 2; 3];
set->contains(2)           /* true */
set->length                /* 3 */
set->insert(4)             /* [1; 2; 3; 4] */
set->without(2)            /* [1; 3] */
```

## 3.7 Function Values

Functions are first-class values with parameter and return types.

**Syntax:**
```walnut
^parameter-type => return-type :: body
```

**Examples:**
```walnut
/* Simple function */
^Integer => Integer :: # * 2

/* Named parameter */
^x: Integer => Integer :: x * 2

/* Multiple parameters (using tuple) */
^[a: Integer, b: Integer] => Integer :: a + b

/* Function returning function */
^Integer => (^String => Integer) :: {
    ^s: String => Integer :: s->length + #
}

/* Function with Result return type */
^String => Result<Integer, NotANumber> :: #->asInteger
```

**Type Inference:**
Functions have function types:

```walnut
^Integer => Integer :: # * 2                    /* Type: ^Integer => Integer */
^x: String => Integer :: x->length              /* Type: ^String => Integer */
^[a: Integer, b: String] => String :: b         /* Type: ^[Integer, String] => String */
```

**Function types as type aliases:**
```walnut
/* Type alias for function type */
MyFunc = ^String => Integer;
MyFuncRetFunc = ^Integer => MyFunc;

/* Using the type */
myFunc = ^s: String => Integer :: s->length;   /* Type: MyFunc */
```

**Parameter variable:**
Inside a function, `#` refers to the parameter:

```walnut
^Integer => Integer :: # * 2           /* # is the integer parameter */
^String => Integer :: #->length        /* # is the string parameter */

/* Named parameter is preferred for clarity */
^x: Integer => Integer :: x * 2
^s: String => Integer :: s->length
```

**Function invocation:**
```walnut
myFunc = ^x: Integer => Integer :: x * 2;
myFunc(5)              /* 10 */
```

## 3.8 Type Values

Type values represent types as first-class values.

**Syntax:**
```walnut
`TypeExpression            /* Short form (backtick) */
type[record-type]          /* Record type value */
type[tuple-type]           /* Tuple type value */
```

**Examples:**
```walnut
/* Basic type values */
`String                    /* Type value for String */
`Integer                   /* Type value for Integer */
`Real                      /* Type value for Real */

/* Complex type values */
`Array<Integer>       /* Type value for integer array */
`Map<String>               /* Type value for string map */
`Set<Real, 1..10>     /* Type value for real set */

/* Tuple type values */
type[Integer, String]      /* Type value for tuple type */
type[Integer, String, ... Real]  /* Open tuple type value */

/* Record type values */
type[a: String, b: Integer]      /* Type value for record type */
type[a: Integer, ... String]     /* Open record type value */

/* Function type values */
`^String => Integer   /* Type value for function type */
`^Integer => Real          /* Function type (short form) */

/* Refined type values */
`Integer[42, -2]           /* Integer subset type */
`Real[1, 3.14]             /* Real subset type */
`String['a', '']           /* String subset type */
`Integer<1..10>            /* Integer range type */
`String<5..10>             /* String length range type */

/* Named type values */
`MyAtom                    /* Atom type value */
`MyEnum                    /* Enumeration type value */
`MyEnum[Value1, Value2]    /* Enumeration subset type */
`MyData                    /* Data type value */

/* Union and intersection types */
`Integer|String            /* Union type value */
`Integer&String            /* Intersection type value */

/* Mutable type values */
`Mutable<String>           /* Mutable string type */
`Mutable<Real>        /* Mutable real type */

/* Result type values */
`Result<Integer, String>   /* Result type value */
```

**Type inference:**
Type values have Type types:

```walnut
`String                    /* Type: Type<String> */
`Integer              /* Type: Type<Integer> */
`Array<Real>               /* Type: Type<Array<Real>> */
type[a: Integer, b: String]  /* Type: Type<[a: Integer, b: String]> */
```

**Using type values:**
```walnut
/* Type checking */
value->isOfType(`String)
value->isOfType(`Integer)

/* Type reflection */
myFunc->type               /* Get function's type */
value->type                /* Get value's type */

/* Hydration */
[1, 2, 3]->hydrateAs(`Array<Integer>)

/* Subtype checking */
`Integer->isSubtypeOf(`Real)
```

## 3.9 Data Values

Data types wrap a value of another type, creating a distinct nominal type.

**Declaration Syntax:**
```walnut
DataName := Type;
```

**Value Syntax:**
```walnut
DataName!value             /* Short form */
```

**Examples:**
```walnut
/* Data type declarations */
MyData := Integer;
ProductId := #Integer<1..>;
UserId := String;

/* Data values */
MyData!42                  /* Type: MyData */
ProductId!15               /* Type: ProductId */
UserId!'user123'           /* Type: UserId */

/* Data with record types */
Point := [x: Real, y: Real];
Point![x: 1.0, y: 2.0]     /* Type: Point */
```

**Type inference:**
Data values have the data type:

```walnut
MyData := Integer;
MyData!42                  /* Type: MyData (not Integer) */
```

**Extracting the wrapped value:**
Data types can define methods to extract or transform the value:

```walnut
MyData := Integer;
MyData ==> Integer :: $$    /* Define extraction */

value = MyData!42;
value->asInteger           /* 42 (unwrapped) */
```

## 3.10 Open and Sealed Values

Open and sealed types are nominal types wrapping another type.

**Declaration Syntax:**
```walnut
/* Open type */
MyInt := #Integer;
OpenName := #[field1: Type1, field2: Type2, ...];

/* Sealed type */
MyInt := $Integer;
SealedName := $[field1: Type1, field2: Type2, ...];
```

**Value Syntax (Constructor):**
```walnut
OpenName[field1: value1, field2: value2]
SealedName[field1: value1, field2: value2]
```

**Examples:**
```walnut
/* Open type declarations */
MyOpen := #[a: Integer, b: Integer];
ProductEvent := #[title: String, price: Real];

/* Open values */
MyOpen[a: 3, b: -2]                    /* Type: MyOpen */
ProductEvent[title: 'Widget', price: 9.99]  /* Type: ProductEvent */

/* Sealed type declarations */
MySealed := $[a: Integer, b: Integer];
ProductState := $[title: String, price: Real, quantity: Integer];

/* Sealed values */
MySealed[a: 3, b: -2]                  /* Type: MySealed */
ProductState[title: 'Widget', price: 9.99, quantity: 10]  /* Type: ProductState */
```

**With validators:**
```walnut
/* Open type with validator */
MyOpen := #[a: Integer, b: Integer] @ MyAtom :: null;

/* Sealed type with validator */
MySealed := $[a: Integer, b: Integer] @ MyAtom :: null;
```

**With constructors:**
```walnut
/* Constructor definition */
MyOpen[a: Real, b: Real] %% MyAtom :: [a: #a->asInteger, b: #b->asInteger];

/* Using constructor */
MyOpen[a: 3.14, b: 2.71]               /* Converts reals to integers */
```

**Field access:**
```walnut
value = MyOpen[a: 3, b: -2];
value.a                                /* 3 */
value.b                                /* -2 */
```

**Difference between Open and Sealed:**
- **Open types** (`#[...]`): The internal value is accessible
- **Sealed types** (`$[...]`): The internal value is not directly accessible except through defined methods

## 3.11 Mutable Values

Mutable values are containers that can be modified.

**Syntax:**
```walnut
mutable{Type, initialValue}
```

**Examples:**
```walnut
/* Basic mutable values */
a = mutable{Integer, 25};
b = mutable{Array<Integer>, [1, 3, 5]};
s = mutable{String, 'Hello'};

/* Nested mutable */
c = mutable{Mutable<Integer>, a};
```

**Type inference:**
```walnut
mutable{Integer, 25}           /* Type: Mutable<Integer> */
mutable{String, 'Hello'}       /* Type: Mutable<String> */
mutable{Array<Integer>, [1, 3, 5]}  /* Type: Mutable<Array<Integer>> */
```

**Operations:**
```walnut
/* Get value */
a = mutable{Integer, 25};
a->value                       /* 25 */

/* Set value */
a->SET(10);
a->value                       /* 10 */

/* Array operations */
arr = mutable{Array<Integer>, [1, 3, 5]};
arr->PUSH(7);                  /* Add element */
arr->POP;                      /* Remove and return last element */
arr->UNSHIFT(9);               /* Add to beginning */
arr->SHIFT;                    /* Remove and return first element */

/* String operations */
str = mutable{String, 'Hello'};
str->APPEND(' World');
str->value                     /* 'Hello World' */

/* Set operations */
set = mutable{Set<Integer>, [2; 5]};
set->ADD(7);                   /* Add element */
set->REMOVE(5);                /* Remove element */
set->CLEAR;                    /* Clear all elements */
```

## 3.12 Error Values

Error values represent error conditions in Result types.

**Syntax:**
```walnut
@'error message'               /* Simple error (short form) */
@ErrorType                     /* Atom error */
@ErrorType[field: value]       /* Record error */
Error('error message')         /* Error constructor (long form) */
```

**Examples:**
```walnut
/* Simple string errors */
@'Not found'                   /* Type: Error<String['Not found']> */
@'Invalid input'               /* Type: Error<String['Invalid input']> */

/* Atom errors */
NotFound := ();
@NotFound                      /* Type: Error<NotFound> */

/* Record errors */
@InvalidDate                   /* Atom error */
@ExternalError[errorType: 'Error', originalError: 'Error', errorMessage: 'Error']

/* Used in Result types */
myFn = ^String => Result<Integer, NotANumber> :: {
    ?when(#->asBoolean) {
        #->asInteger
    } ~ {
        @NotANumber
    }
};
```

**Type inference:**
```walnut
@'error'                       /* Type: Error<String['error']> */
@NotFound                      /* Type: Error<NotFound> */
```

**Error handling:**
```walnut
/* Early return on error */
result = someValue=>methodThatMayFail;  /* Returns early if error */

/* Check if error */
?whenIsError(result) {
    /* Handle error */
} ~ {
    /* Handle success */
};

/* No error assertion */
?noError(result)               /* Returns early if result is error */
?noExternalError(result)       /* Returns early if result is external error */
```

## 3.13 Constant Expressions

Constant expressions explicitly mark values as constants using `val`.

**Syntax:**
```walnut
val{expression}                /* Constant value */
val[element1, element2, ...]   /* Constant tuple */
val[key1: value1, key2: value2]  /* Constant record */
```

**Examples:**
```walnut
/* Constant values */
val{42}                        /* Constant integer */
val{false}                     /* Constant boolean */
val{'hello'}                   /* Constant string */

/* Constant collections */
val[1, 2, 3]                   /* Constant tuple */
val[a: 1, b: 2]                /* Constant record */
val[1; 2; 3]                   /* Constant set (if supported) */
```

**Use case:**
The `val` syntax makes it explicit that a value should be treated as a compile-time constant. This is useful for:

1. **Documentation**: Making it clear that a value is constant
2. **Optimization**: Allowing the compiler to optimize constant values
3. **Type refinement**: Ensuring the most specific type is inferred

**Example from demo-all.nut:**
```walnut
allConstants = val[
    atom: MyAtom,
    booleanTrue: true,
    booleanFalse: false,
    enumeration: MyEnum.Value1,
    integer: 42,
    real: 3.14,
    string: 'hi!',
    null: null,
    emptyTuple: [],
    tuple: [1, 'hi!'],
    emptyRecord: [:],
    record: [a: 1, b: 'hi!'],
    emptySet: [;],
    set: [1; 'hi!'],
    type: `String,
    mutable: mutable{String, 'mutable'},
    error: @'error',
    data: MyData!42,
    function: ^Any => Any :: 'function body'
];
```

## 3.14 Special Values

### 3.14.1 RegExp Values

Regular expression values for pattern matching.

**Syntax:**
```walnut
RegExp('pattern')
```

**Examples:**
```walnut
r1 = RegExp('/\d+/');          /* Match digits */
r2 = RegExp('/sd.a/');         /* Match pattern */
r3 = RegExp('/\d(\d+)/');      /* Capture group */

/* Using RegExp */
r1->matchString('1423')        /* Match string against pattern */
```

### 3.14.2 Uuid Values

UUID (Universally Unique Identifier) values.

**Syntax:**
```walnut
Random->uuid                   /* Generate random UUID */
Uuid('uuid-string')            /* Create from string */
```

**Examples:**
```walnut
u1 = Random->uuid;             /* Random UUID */
u2 = Uuid('67c17642-2788-40e5-b07e-0198dabe63e4');  /* From string */
```

### 3.14.3 Shape Values

Shape values provide a way to treat different types uniformly when they can be cast to the shape.

**Syntax:**
```walnut
Shape<T>                       /* Shape type */
value->shape(`TargetType)      /* Extract shape */
```

**Examples:**
```walnut
MyReal := Real<4..12>;
MyOwnReal := Real<0..100>;
MyUnrelated := #[x: Real];
MyUnrelated ==> Real :: $x;

shapeFn = ^[Shape<Real>, Shape<Real>] => Real :: {
    {#0->shape(`Real)} + {#1->shape(`Real)}
};

shapeFn[MyReal!4.1, MyOwnReal!9.2]     /* Works: 13.3 */
shapeFn[42, MyUnrelated[x: 9.2]]       /* Works: 51.2 */
```

## 3.15 Type Inference Summary

Walnut performs sophisticated type inference on all values:

| Value | Example | Inferred Type |
|-------|---------|---------------|
| Integer | `42` | `Integer[42]` |
| Real | `3.14` | `Real[3.14]` |
| String | `'hello'` | `String['hello']` (also `String<5..5>`) |
| Boolean | `true` | `True` |
| Null | `null` | `Null` |
| Atom | `MyAtom` | `MyAtom` |
| Enumeration | `Suit.Hearts` | `Suit[Hearts]` |
| Tuple | `[1, 'hi']` | `[Integer[1], String['hi']]` |
| Record | `[a: 1]` | `[a: Integer[1]]` |
| Set | `[1; 2]` | `Set<Integer[1, 2], 2..2>` |
| Function | `^Integer => Integer :: # * 2` | `^Integer => Integer` |
| Type | `` `String `` | `Type<String>` |
| Data | `MyData!42` | `MyData` |
| Open/Sealed | `MyOpen[a: 1]` | `MyOpen` |
| Mutable | `mutable{Integer, 5}` | `Mutable<Integer>` |
| Error | `@'error'` | `Error<String['error']>` |

The type system uses these refined types for:
- **Type checking**: Ensuring type safety at compile time
- **Type narrowing**: Using control flow to refine types
- **Subtype relations**: Determining when one type can be used in place of another
- **Method resolution**: Finding applicable methods based on precise types

## 3.16 Value Categories

All Walnut values fall into these categories:

1. **Immutable primitive values**: Integer, Real, String, Boolean, Null
2. **Immutable composite values**: Tuple, Record, Set
3. **Singleton values**: Atom, Enumeration values
4. **Nominal values**: Data, Open, Sealed (wrap other values)
5. **First-class functions**: Function values
6. **Meta values**: Type values (types as values)
7. **Container values**: Mutable (allows mutation)
8. **Error values**: Error (for Result types)
9. **Special values**: RegExp, Uuid, Shape

All values except Mutable are immutable by default. This ensures referential transparency and makes reasoning about code easier.
