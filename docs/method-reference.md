# Walnut Method Reference

This document provides a comprehensive reference for all built-in methods available on Walnut language types. Methods are grouped by the type they operate on.

**Notation**: Method signatures use the form `TargetType->methodName(ParamType => ReturnType)`. For operator methods, the operator symbol is shown in parentheses. Methods marked with `[REVIEW]` have complex type-refinement logic in their implementations.

## Table of Contents

- [1. Any Methods](#1-any-methods)
  - [1.1 Equality](#11-equality)
  - [1.2 Type Operations](#12-type-operations)
  - [1.3 Type Casts](#13-type-casts)
  - [1.4 Serialization](#14-serialization)
  - [1.5 Debugging](#15-debugging)
  - [1.6 Error Handling](#16-error-handling)
  - [1.7 Function Application](#17-function-application)
- [2. Boolean Methods](#2-boolean-methods)
  - [2.1 Logical Operations](#21-logical-operations)
  - [2.2 Type Casts](#22-type-casts)
  - [2.3 True Subtype Refinements](#23-true-subtype-refinements)
  - [2.4 False Subtype Refinements](#24-false-subtype-refinements)
- [3. Null Methods](#3-null-methods)
- [4. Enumeration Methods](#4-enumeration-methods)
- [5. Integer Methods](#5-integer-methods)
  - [5.1 Arithmetic](#51-arithmetic)
  - [5.2 Comparison](#52-comparison)
  - [5.3 Bitwise Operations](#53-bitwise-operations)
  - [5.4 Math Functions](#54-math-functions)
  - [5.5 Range Generation](#55-range-generation)
  - [5.6 Type Casts](#56-type-casts)
  - [5.7 Other](#57-other)
- [6. Real Methods](#6-real-methods)
  - [6.1 Arithmetic](#61-arithmetic)
  - [6.2 Comparison](#62-comparison)
  - [6.3 Math Functions](#63-math-functions)
  - [6.4 Rounding](#64-rounding)
  - [6.5 Type Casts](#65-type-casts)
- [7. String Methods](#7-string-methods)
  - [7.1 Concatenation and Repetition](#71-concatenation-and-repetition)
  - [7.2 Comparison](#72-comparison)
  - [7.3 Length and Properties](#73-length-and-properties)
  - [7.4 Case Conversion](#74-case-conversion)
  - [7.5 Trimming](#75-trimming)
  - [7.6 Searching](#76-searching)
  - [7.7 Extraction](#77-extraction)
  - [7.8 Modification](#78-modification)
  - [7.9 Padding](#79-padding)
  - [7.10 Pattern Matching](#710-pattern-matching)
  - [7.11 HTML and Output](#711-html-and-output)
  - [7.12 JSON](#712-json)
  - [7.13 Type Casts](#713-type-casts)
  - [7.14 Constructors](#714-constructors)
- [8. Array Methods](#8-array-methods)
  - [8.1 Properties](#81-properties)
  - [8.2 Searching](#82-searching)
  - [8.3 Adding Elements](#83-adding-elements)
  - [8.4 Removing Elements](#84-removing-elements)
  - [8.5 Transformation](#85-transformation)
  - [8.6 Slicing and Padding](#86-slicing-and-padding)
  - [8.7 Aggregation](#87-aggregation)
  - [8.8 Conversion](#88-conversion)
  - [8.9 Operators](#89-operators)
  - [8.10 Partition](#810-partition)
- [9. Map Methods](#9-map-methods)
  - [9.1 Properties](#91-properties)
  - [9.2 Searching](#92-searching)
  - [9.3 Modification](#93-modification)
  - [9.4 Transformation](#94-transformation)
  - [9.5 Conversion](#95-conversion)
- [10. Set Methods](#10-set-methods)
  - [10.1 Properties](#101-properties)
  - [10.2 Modification](#102-modification)
  - [10.3 Set Operations](#103-set-operations)
  - [10.4 Set Comparisons](#104-set-comparisons)
  - [10.5 Transformation](#105-transformation)
  - [10.6 Conversion](#106-conversion)
- [11. Mutable Methods](#11-mutable-methods)
  - [11.1 Value Access](#111-value-access)
  - [11.2 Mutation](#112-mutation)
  - [11.3 Collection Mutations (Array/Tuple)](#113-collection-mutations-arraytuple)
  - [11.4 Set/Map Mutations](#114-setmap-mutations)
  - [11.5 Type Casts](#115-type-casts)
- [12. Function Methods](#12-function-methods)
- [13. Result Methods](#13-result-methods)
- [14. Open Type Methods](#14-open-type-methods)
- [15. Data Type Methods](#15-data-type-methods)
- [16. Sealed Type Methods](#16-sealed-type-methods)
- [17. Record Methods](#17-record-methods)
- [18. Tuple Methods](#18-tuple-methods)
- [19. Type Methods](#19-type-methods)
  - [19.1 Type Introspection](#191-type-introspection)
  - [19.2 Type Construction](#192-type-construction)
  - [19.3 Type Operators](#193-type-operators)
- [20. Bytes Methods](#20-bytes-methods)
  - [20.1 Properties](#201-properties)
  - [20.2 Search and Test](#202-search-and-test)
  - [20.3 Operators](#203-operators)
  - [20.4 Slicing and Splitting](#204-slicing-and-splitting)
  - [20.5 Concatenation and Modification](#205-concatenation-and-modification)
  - [20.6 Conversion](#206-conversion)
- [21. JsonValue Methods](#21-jsonvalue-methods)
- [22. RegExp Methods](#22-regexp-methods)
- [23. Clock Methods](#23-clock-methods)
- [24. Random Methods](#24-random-methods)
- [25. PasswordString Methods](#25-passwordstring-methods)
- [26. File Methods](#26-file-methods)
- [27. RoutePattern Methods](#27-routepattern-methods)
- [28. DependencyContainer Methods](#28-dependencycontainer-methods)
- [29. EventBus Methods](#29-eventbus-methods)

---

## 1. Any Methods

These methods are available on all values, regardless of type.

### 1.1 Equality

**`binaryEqual`** (`==`) - Test two values for equality
```walnut
Any->binaryEqual(Any => Boolean)

42 == 42;               /* true */
'hello' == 'world';     /* false */
```

**`binaryNotEqual`** (`!=`) - Test two values for inequality
```walnut
Any->binaryNotEqual(Any => Boolean)

42 != 99;               /* true */
'hello' != 'hello';     /* false */
```

### 1.2 Type Operations

**`type`** - Get the type of a value as a type value
```walnut
Any->type(Null => Type)

42->type;               /* `Integer[42] */
'hello'->type;          /* `String['hello'] */
```

**`isOfType`** - Check whether a value is of a given type
```walnut
Any->isOfType(Type => Boolean)

42->isOfType(`Integer);         /* true */
42->isOfType(`String);          /* false */
```

**`castAs`** [REVIEW] - Cast a value to a different type, performing conversion if possible
```walnut
Any->castAs(Type => Result<TargetType, CastNotAvailable>)

42->castAs(`String);            /* '42' */
'hello'->castAs(`Integer);     /* @CastNotAvailable */
```

**`shape`** [REVIEW] - Convert a value to match a structural type shape
```walnut
Any->shape(Type => Result<TargetType, CastNotAvailable>)

[a: 1, b: 2, c: 3]->shape(`[a: Integer, b: Integer]);  /* [a: 1, b: 2] */
```

**`construct`** [REVIEW] - Construct an instance of an open, sealed, or enumeration type from a value
```walnut
Any->construct(Type => TargetType | Result<TargetType, ErrorType>)

42->construct(`PositiveInteger);       /* PositiveInteger(42) */
'Red'->construct(`Colour);            /* Colour.Red */
```

### 1.3 Type Casts

**`asBoolean`** - Convert any value to a boolean (by default returns `true` but it can be redefined by types to provide more specific conversions)
```walnut
Any->asBoolean(Any => Boolean)

42->asBoolean;          /* true */
null->asBoolean;        /* true */
```

**`asJsonValue`** - Convert a value to a JSON-compatible value
```walnut
Any->asJsonValue(Null => Result<JsonValue, InvalidJsonValue>)

42->asJsonValue;            /* 42 */
```

**`asMutableOfType`** - [REVIEW] Wrap a value in a mutable container of the given type
```walnut
Any->asMutableOfType(Type => Result<Mutable, CastNotAvailable>)

42->asMutableOfType(`Integer);  /* Mutable<Integer>(42) */
```

### 1.4 Serialization

**`jsonStringify`** - Convert a value to a JSON string representation
```walnut
Any->jsonStringify(Null => String | Result<String, InvalidJsonValue>)

42->jsonStringify;           /* '42' */
[a: 1, b: 2]->jsonStringify;  /* '{"a":1,"b":2}' */
```

**`printed`** - Get a printable representation of a value
```walnut
Any->printed(Any => String)

42->printed;            /* '42' */
true->printed;          /* 'true' */
```

### 1.5 Debugging

**`DUMP`** - Print a value to standard output and return it (for debugging)
```walnut
Any->DUMP(Null | [html: ?Boolean, newLine: ?Boolean] => Any)

42->DUMP;                         /* prints '42', returns 42 */
42->DUMP[newLine: true];          /* prints '42\n', returns 42 */
42->DUMP[html: true];             /* prints HTML-escaped value, returns 42 */
```

### 1.6 Error Handling

**`binaryOrElse`** (`??`) - Return the target value if it is not an error, otherwise return the fallback value
```walnut
Result<T, E>->binaryOrElse<K>(K => T | K)

result ?? 'default';              /* value from result, or 'default' if error */
```

**`ifError`** [REVIEW] - Handle an error by providing a callback that transforms the error value
```walnut
Result<T, E>->ifError(^E => R => T | R)

result->ifError(^e: MyError => 0);  /* value from result, or 0 if error */
```

**`when`** [REVIEW] - Handle both success and error cases with separate callbacks
```walnut
Result<T, E>->when([success: ^T => R1, error: ^E => R2] => R1 | R2)

result->when[
    success: ^v: Integer => v + 1,
    error: ^e: MyError => 0
];
```

**`errorAsExternal`** - Wrap any error as an `ExternalError`, preserving the original error information
```walnut
Result<T, E>->errorAsExternal(Null | String => Result<T, ExternalError>)

result->errorAsExternal;              /* wraps error as ExternalError with default message */
result->errorAsExternal('Failed');    /* wraps error as ExternalError with custom message */
result *> ('Failed');                 /* shorthand syntax */
```

### 1.7 Function Application

**`apply`** - Apply a function to the target value (pipe the value into the function)
```walnut
Any<T>->apply(^T => R => R)

42->apply(^x: Integer => x + 1);   /* 43 */
```

## 2. Boolean Methods

### 2.1 Logical Operations

**`binaryBitwiseAnd`** (`&`) - Logical AND of two boolean values
```walnut
Boolean->binaryBitwiseAnd(Boolean => Boolean)

true & true;            /* true */
true & false;           /* false */
false & true;           /* false */
false & false;          /* false */
```

When either operand is statically known to be `False`, the return type narrows to `False`.

**`binaryBitwiseOr`** (`|`) - Logical OR of two boolean values
```walnut
Boolean->binaryBitwiseOr(Boolean => Boolean)

true | true;            /* true */
true | false;           /* true */
false | true;           /* true */
false | false;          /* false */
```

When either operand is statically known to be `True`, the return type narrows to `True`.

**`binaryBitwiseXor`** (`^`) - Logical XOR of two boolean values
```walnut
Boolean->binaryBitwiseXor(Boolean => Boolean)

true ^ true;            /* false */
true ^ false;           /* true */
false ^ true;           /* true */
false ^ false;          /* false */
```

When both operands have known static types, the return type narrows accordingly (e.g. `True ^ False => True`).

**`unaryBitwiseNot`** (`~`) - Logical NOT of a boolean value
```walnut
Boolean->unaryBitwiseNot(Null => Boolean)

~true;                  /* false */
~false;                 /* true */
```

When the target is statically known to be `True`, returns `False`, and vice versa.

### 2.2 Type Casts

**`asBoolean`** - Identity conversion, returns the boolean value unchanged
```walnut
Boolean->asBoolean(Null => Boolean)

true->asBoolean;        /* true */
false->asBoolean;       /* false */
```

**`asInteger`** - Convert boolean to integer (`true` -> `1`, `false` -> `0`)
```walnut
Boolean->asInteger(Null => Integer[0, 1])

true->asInteger;        /* 1 */
false->asInteger;       /* 0 */
```

**`asReal`** - Convert boolean to real number (`true` -> `1.0`, `false` -> `0.0`)
```walnut
Boolean->asReal(Null => Real[0, 1])

true->asReal;           /* 1 */
false->asReal;          /* 0 */
```

**`asString`** - Convert boolean to string (`true` -> `'true'`, `false` -> `'false'`)
```walnut
Boolean->asString(Null => String['true', 'false'])

true->asString;         /* 'true' */
false->asString;        /* 'false' */
```

**`asJsonValue`** - Convert boolean to a JSON value (identity, booleans are valid JSON)
```walnut
Boolean->asJsonValue(Null => JsonValue)

true->asJsonValue;      /* true */
false->asJsonValue;     /* false */
```

### 2.3 True Subtype Refinements

When the target is statically known to be `True`, the following methods provide narrower return types:

**`asBoolean`** - Returns `True` (narrowed from `Boolean`)
```walnut
True->asBoolean(Null => True)

true->asBoolean;        /* true */
```

**`asInteger`** - Returns `Integer[1]` (narrowed from `Integer[0, 1]`)
```walnut
True->asInteger(Null => Integer[1])

true->asInteger;        /* 1 */
```

**`asReal`** - Returns `Real[1]` (narrowed from `Real[0, 1]`)
```walnut
True->asReal(Null => Real[1])

true->asReal;           /* 1 */
```

**`asString`** - Returns `String['true']` (narrowed from `String['true', 'false']`)
```walnut
True->asString(Null => String['true'])

true->asString;         /* 'true' */
```

**`unaryBitwiseNot`** (`~`) - Returns `False` (narrowed from `Boolean`)
```walnut
True->unaryBitwiseNot(Null => False)

~true;                  /* false */
```

### 2.4 False Subtype Refinements

When the target is statically known to be `False`, the following methods provide narrower return types:

**`asBoolean`** - Returns `False` (narrowed from `Boolean`)
```walnut
False->asBoolean(Null => False)

false->asBoolean;       /* false */
```

**`asInteger`** - Returns `Integer[0]` (narrowed from `Integer[0, 1]`)
```walnut
False->asInteger(Null => Integer[0])

false->asInteger;       /* 0 */
```

**`asReal`** - Returns `Real[0` (narrowed from `Real[0, 1]`)
```walnut
False->asReal(Null => Real[0])

false->asReal;          /* 0 */
```

**`asString`** - Returns `String['false']` (narrowed from `String['true', 'false']`)
```walnut
False->asString(Null => String['false'])

false->asString;        /* 'false' */
```

**`unaryBitwiseNot`** (`~`) - Returns `True` (narrowed from `Boolean`)
```walnut
False->unaryBitwiseNot(Null => True)

~false;                 /* true */
```

## 3. Null Methods

**`asBoolean`** - Convert null to boolean (always returns `false`)
```walnut
Null->asBoolean(Null => False)

null->asBoolean;        /* false */
```

**`asInteger`** - Convert null to integer (always returns `0`)
```walnut
Null->asInteger(Null => Integer[0])

null->asInteger;        /* 0 */
```

**`asReal`** - Convert null to real number (always returns `0.0`)
```walnut
Null->asReal(Null => Real[0])

null->asReal;           /* 0 */
```

**`asString`** - Convert null to string (always returns `'null'`)
```walnut
Null->asString(Null => String['null'])

null->asString;         /* 'null' */
```

**`asJsonValue`** - Convert null to a JSON value (identity, null is valid JSON)
```walnut
Null->asJsonValue(Null => JsonValue)

null->asJsonValue;      /* null */
```

## 4. Enumeration Methods

**`enumeration`** - Get the enumeration type of an enumeration value
```walnut
EnumerationSubset->enumeration(Null => Type)

Colour.Red->enumeration;     /* `Colour */
```

**`textValue`** - Get the name of an enumeration value as a string
```walnut
EnumerationSubset->textValue(Null => String<a..b>) 

Colour.Red->textValue;       /* 'Red' */
```

The return type's string length range `<a..b>` is computed from the lengths of the enumeration value names in the subset.

**`asJsonValue`** - Convert an enumeration value to a JSON-compatible string
```walnut
EnumerationSubset->asJsonValue(Null => JsonValue)

Colour.Red->asJsonValue;     /* 'Red' */
```
The default behavior is to convert enumeration values to their text names as JSON strings, but this can be overridden by defining an explicit cast method.

## 5. Integer Methods

### 5.1 Arithmetic

Common notes:
For all the arithmetic operations the return type is refined based on the operand types (e.g. `Integer[2] + Integer[3] => Integer[5]`).
For the division, integer division, and modulo operations, if the divisor is statically known to be non-zero, the return type does not include the `NotANumber` possibility.

**`binaryPlus`** (`+`) - Add an integer or real to an integer
```walnut
Integer->binaryPlus(Integer => Integer)
Integer->binaryPlus(Real => Real)

3 + 4;      /* 7 */
3 + 1.5;    /* 4.5 */
```

**`binaryMinus`** (`-`) - Subtract an integer or real from an integer
```walnut
Integer->binaryMinus(Integer => Integer)
Integer->binaryMinus(Real => Real)

10 - 3;     /* 7 */
10 - 2.5;   /* 7.5 */
```

**`binaryMultiply`** (`*`) - Multiply an integer by an integer or real
```walnut
Integer->binaryMultiply(Integer => Integer)
Integer->binaryMultiply(Real => Real)

4 * 3;      /* 12 */
4 * 1.5;    /* 6.0 */
```

**`binaryDivide`** (`/`) - Divide an integer by an integer or real, returning a real
```walnut
Integer->binaryDivide(Integer => Real | Result<Real, NotANumber>)
Integer->binaryDivide(Real => Real | Result<Real, NotANumber>)

10 / 3;     /* 3.3333333333333 */
10 / 0;     /* @NotANumber */
```
If the divisor is guaranteed to be non-zero (e.g. `Integer[2]`), the return type is `Real` without the `NotANumber` possibility.

**`binaryIntegerDivide`** (`//`) - Integer division of two integers
```walnut
Integer->binaryIntegerDivide(Integer => Integer | Result<Integer, NotANumber>)

10 // 3;    /* 3 */
10 // 0;    /* @NotANumber */
```
If the divisor is guaranteed to be non-zero (e.g. `Integer[2]`), the return type is `Integer` without the `NotANumber` possibility.

**`binaryModulo`** (`%`) - Remainder after division by an integer or real
```walnut
Integer->binaryModulo(Integer => Integer | Result<Integer, NotANumber>)
Integer->binaryModulo(Real => Real | Result<Real, NotANumber>)

10 % 3;     /* 1 */
10 % 0;     /* @NotANumber */
```
If the divisor is guaranteed to be non-zero (e.g. `Integer[3]`), the return type does not include the `NotANumber` possibility.

**`binaryPower`** (`**`) - Raise an integer to an integer or real power
```walnut
Integer->binaryPower(Integer => Integer)
Integer->binaryPower(Real => Real)

2 ** 10;    /* 1024 */
3 ** 0.5;   /* 1.7320508075689 */
```

**`unaryMinus`** (`-`) - Negate an integer
```walnut
Integer->unaryMinus(Null => Integer)

-5;         /* -5 */
-(3);       /* -3 */
```

**`unaryPlus`** (`+`) - Identity operation on an integer
```walnut
Integer->unaryPlus(Null => Integer)

+5;         /* 5 */
```

### 5.2 Comparison

**`binaryGreaterThan`** (`>`) - Check if an integer is greater than another number
```walnut
Integer->binaryGreaterThan(Integer|Real => Boolean)

5 > 3;      /* true */
3 > 5;      /* false */
```

**`binaryGreaterThanEqual`** (`>=`) - Check if an integer is greater than or equal to another number
```walnut
Integer->binaryGreaterThanEqual(Integer|Real => Boolean)

5 >= 5;     /* true */
3 >= 5;     /* false */
```

**`binaryLessThan`** (`<`) - Check if an integer is less than another number
```walnut
Integer->binaryLessThan(Integer|Real => Boolean)

3 < 5;      /* true */
5 < 3;      /* false */
```

**`binaryLessThanEqual`** (`<=`) - Check if an integer is less than or equal to another number
```walnut
Integer->binaryLessThanEqual(Integer|Real => Boolean)

5 <= 5;     /* true */
5 <= 3;     /* false */
```

### 5.3 Bitwise Operations

All bitwise operations require non-negative integers within the platform integer range (`Integer<0..MAX_INT>`).
`MAX_INT` is `9223372036854775807`

**`binaryBitwiseAnd`** (`&`) - Bitwise AND of two non-negative integers
```walnut
Integer<0..MAX_INT>->binaryBitwiseAnd(Integer<0..MAX_INT> => Integer<0..MAX_INT>)

12 & 10;  /* 8 */
```

**`binaryBitwiseOr`** (`|`) - Bitwise OR of two non-negative integers
```walnut
Integer<0..MAX_INT>->binaryBitwiseOr(Integer<0..MAX_INT> => Integer<0..MAX_INT>)

12 | 10;  /* 14 */
```

**`binaryBitwiseXor`** (`^`) - Bitwise XOR of two non-negative integers
```walnut
Integer<0..MAX_INT>->binaryBitwiseXor(Integer<0..MAX_INT> => Integer<0..MAX_INT>)

12 ^ 10;  /* 6 */
```

**`unaryBitwiseNot`** (`~`) - Bitwise NOT of a non-negative integer (63-bit)
```walnut
Integer<0..MAX_INT>->unaryBitwiseNot(Null => Integer<0..MAX_INT>)

~0;       /* 9223372036854775807 */
~255;     /* 9223372036854775552 */
```

### 5.4 Math Functions
Once again the return types of these functions are refined based on the input types (e.g. `Integer[-20..10]->abs => Integer[0..20]`).

**`abs`** - Absolute value of an integer
```walnut
Integer->abs(Null => Integer<0..>)

(-5)->abs;  /* 5 */
3->abs;     /* 3 */
```

**`square`** - Square of an integer
```walnut
Integer->square(Null => Integer<0..>)

5->square;  /* 25 */
(-3)->square; /* 9 */
```

**`clamp`** - Clamp an integer to a range defined by min and/or max
```walnut
Integer->clamp([min: OptionalKey<Integer|Real>, max: OptionalKey<Integer|Real>] => Integer|Real|Result<Integer|Real, InvalidIntegerRange|InvalidRealRange>)

5->clamp[min: 1, max: 10];   /* 5 */
15->clamp[min: 1, max: 10];  /* 10 */
0->clamp[min: 1, max: 10];   /* 1 */
5->clamp[min: 10, max: 1];   /* @InvalidIntegerRange[[min: 10, max: 1]] */
```
The method returns `Integer` if the min..max range is inferred to be valid.

### 5.5 Range Generation
In both methods the Array length range is inferred from the input types.

**`upTo`** - Generate an ascending array of integers from target up to the parameter (inclusive)
```walnut
Integer->upTo(Integer => Array<Integer>)

1->upTo(5);   /* [1, 2, 3, 4, 5] */
5->upTo(3);   /* [] */
```

**`downTo`** - Generate a descending array of integers from target down to the parameter (inclusive)
```walnut
Integer->downTo(Integer => Array<Integer>)

5->downTo(1); /* [5, 4, 3, 2, 1] */
3->downTo(5); /* [] */
```

### 5.6 Type Casts

**`asBoolean`** - Convert an integer to boolean (0 is false, non-zero is true)
```walnut
Integer->asBoolean(Null => Boolean)

0->asBoolean;  /* false */
1->asBoolean;  /* true */
(-3)->asBoolean; /* true */
```

**`asInteger`** - Identity cast (returns the integer itself)
```walnut
Integer->asInteger(Null => Integer)

42->asInteger;  /* 42 */
```

**`asReal`** - Convert an integer to a real number
```walnut
Integer->asReal(Null => Real)

42->asReal;   /* 42.0 */
```

**`asString`** - Convert an integer to its string representation. The length of the resulting string is inferred from the number of digits in the integer (e.g. `Integer[0..999]->asString => String[1..3]`).
```walnut
Integer->asString(Null => String)

42->asString;   /* '42' */
(-7)->asString; /* '-7' */
```

**`asJsonValue`** - Convert an integer to a JSON value
```walnut
Integer->asJsonValue(Null => JsonValue)

42->asJsonValue;  /* 42 */
```

### 5.7 Other

**`chr`** - Convert an integer code point (0-255) to a single-byte string
```walnut
Integer<0..255>->chr(Null => Bytes<1>)

65->chr;    /* 'A' */
48->chr;    /* '0' */
```

**`digits`** - Split a non-negative integer into an array of its individual digits. The length of the resulting array is inferred from the number of digits in the integer (e.g. `Integer[0..999]->digits => Array<Integer>[1..3]`).
```walnut
Integer<0..>->digits(Null => Array<Integer>)

1234->digits;  /* [1, 2, 3, 4] */
0->digits;     /* [0] */
```

---

## 6. Real Methods


### 6.1 Arithmetic
Common notes:
For all the arithmetic operations the return type is refined based on the operand types (e.g. `Rea[3.14] + Real[3] => Real[6.14]`).
For the division, and modulo operations, if the divisor is statically known to be non-zero, the return type does not include the `NotANumber` possibility.

**`binaryPlus`** (`+`) - Add a number to a real
```walnut
Real->binaryPlus(Integer|Real => Real)

3.5 + 1.5;   /* 5.0 */
3.5 + 2;     /* 5.5 */
```

**`binaryMinus`** (`-`) - Subtract a number from a real
```walnut
Real->binaryMinus(Integer|Real => Real)

5.5 - 1.5;   /* 4.0 */
5.5 - 2;     /* 3.5 */
```

**`binaryMultiply`** (`*`) - Multiply a real by a number
```walnut
Real->binaryMultiply(Integer|Real => Real)

2.5 * 4.0;   /* 10.0 */
2.5 * 3;     /* 7.5 */
```

**`binaryDivide`** (`/`) - Divide a real by a number
```walnut
Real->binaryDivide(Integer|Real => Real | Result<Real, NotANumber>)

7.5 / 2.5;   /* 3.0 */
1.0 / 0;     /* @NotANumber */
```

**`binaryModulo`** (`%`) - Remainder after division of a real by a number
```walnut
Real->binaryModulo(Integer|Real => Real | Result<Real, NotANumber>)

7.5 % 2.0;   /* 1.5 */
1.0 % 0;     /* @NotANumber */
```

**`binaryPower`** (`**`) - Raise a real to a power
```walnut
Real->binaryPower(Integer|Real => Real)

2.0 ** 3;     /* 8.0 */
4.0 ** 0.5;   /* 2.0 */
```

**`unaryMinus`** (`-`) - Negate a real number
```walnut
Real->unaryMinus(Null => Real)

-3.14;        /* -3.14 */
-(2.5);       /* -2.5 */
```

**`unaryPlus`** (`+`) - Identity operation on a real number
```walnut
Real->unaryPlus(Null => Real)

+3.14;        /* 3.14 */
```

### 6.2 Comparison

**`binaryGreaterThan`** (`>`) - Check if a real is greater than another number
```walnut
Real->binaryGreaterThan(Integer|Real => Boolean)

3.5 > 3.0;   /* true */
3.0 > 3.5;   /* false */
```

**`binaryGreaterThanEqual`** (`>=`) - Check if a real is greater than or equal to another number
```walnut
Real->binaryGreaterThanEqual(Integer|Real => Boolean)

3.5 >= 3.5;  /* true */
3.0 >= 3.5;  /* false */
```

**`binaryLessThan`** (`<`) - Check if a real is less than another number
```walnut
Real->binaryLessThan(Integer|Real => Boolean)

3.0 < 3.5;   /* true */
3.5 < 3.0;   /* false */
```

**`binaryLessThanEqual`** (`<=`) - Check if a real is less than or equal to another number
```walnut
Real->binaryLessThanEqual(Integer|Real => Boolean)

3.5 <= 3.5;  /* true */
3.5 <= 3.0;  /* false */
```

### 6.3 Math Functions
Once again the return types of these functions are refined based on the input types (e.g. Real[-13.14..10]->abs => Real[0..13.14]).
Methods like `sqrt` and `ln` that have domain restrictions return a `NotANumber` error for out-of-domain inputs, but if the input is statically known to be within the valid domain, the return type does not include the error possibility.

**`abs`** - Absolute value of a real number
```walnut
Real->abs(Null => Real<0..>)

(-3.14)->abs; /* 3.14 */
2.5->abs;     /* 2.5 */
```

**`square`** - Square of a real number
```walnut
Real->square(Null => Real<0..>)

3.0->square;    /* 9.0 */
(-2.5)->square; /* 6.25 */
```

**`sqrt`** - Square root of a real number
```walnut
Real->sqrt(Null => Result<Real<0..>, NotANumber>)

9.0->sqrt;    /* 3.0 */
2.0->sqrt;    /* 1.4142135623731 */
(-1.0)->sqrt; /* @NotANumber */
```

**`ln`** - Natural logarithm of a real number
```walnut
Real->ln(Null => Result<Real, NotANumber>)

1.0->ln;      /* 0.0 */
2.718281828->ln; /* ~1.0 */
(-1.0)->ln;   /* @NotANumber */
0.0->ln;      /* @NotANumber */
```

**`sign`** - Sign of a real number (-1, 0, or 1)
```walnut
Real->sign(Null => Integer[-1, 0, 1])

3.14->sign;   /* 1 */
0.0->sign;    /* 0 */
(-2.5)->sign; /* -1 */
```

**`frac`** - Fractional part of a real number (the part after the decimal point, preserving sign)
```walnut
Real->frac(Null => Real<(-1..1)>)

3.75->frac;   /* 0.75 */
(-3.75)->frac; /* -0.75 */
5.0->frac;    /* 0.0 */
```

**`clamp`** - Clamp a real number to a range defined by min and/or max
```walnut
Real->clamp([min: ?Real, max: ?Real] => Real|Result<Real, InvalidRealRange>)

5.5->clamp[min: 1.0, max: 10.0];   /* 5.5 */
15.0->clamp[min: 1.0, max: 10.0];  /* 10.0 */
0.5->clamp[min: 1.0, max: 10.0];   /* 1.0 */
5.0->clamp[min: 10.0, max: 1.0];   /* @InvalidRealRange[[min: 10.0, max: 1.0]] */
```
The method returns `Real` if the min..max range is inferred to be valid.

### 6.4 Rounding
For all methods in this section the return type range is inferred from the input type (e.g. `Real[-3.5..3.5]->floor => Integer[-4..3]`).

**`floor`** - Round a real number down to the nearest integer
```walnut
Real->floor(Null => Integer)

3.7->floor;   /* 3 */
(-3.2)->floor; /* -4 */
```

**`ceil`** - Round a real number up to the nearest integer
```walnut
Real->ceil(Null => Integer)

3.2->ceil;    /* 4 */
(-3.7)->ceil; /* -3 */
```

**`roundAsInteger`** - Round a real number to the nearest integer (half away from zero)
```walnut
Real->roundAsInteger(Null => Integer)

3.5->roundAsInteger;  /* 4 */
3.4->roundAsInteger;  /* 3 */
(-3.5)->roundAsInteger; /* -4 */
```

**`roundAsDecimal`** - Round a real number to a specified number of decimal places
```walnut
Real->roundAsDecimal(Integer<0..> => Real)

3.14159->roundAsDecimal(2);  /* 3.14 */
3.145->roundAsDecimal(2);    /* 3.15 */
```

### 6.5 Type Casts

**`asBoolean`** - Convert a real to boolean (0.0 is false, non-zero is true)
```walnut
Real->asBoolean(Null => Boolean)

0.0->asBoolean;  /* false */
1.5->asBoolean;  /* true */
(-0.1)->asBoolean; /* true */
```

**`asInteger`** - Convert a real to an integer by truncating toward zero
```walnut
Real->asInteger(Null => Integer)

3.7->asInteger;   /* 3 */
(-3.7)->asInteger; /* -3 */
```

**`asReal`** - Identity cast (returns the real itself)
```walnut
Real->asReal(Null => Real)

3.14->asReal; /* 3.14 */
```

**`asString`** - Convert a real to its string representation
```walnut
Real->asString(Null => String)

3.14->asString;   /* '3.14' */
(-0.5)->asString; /* '-0.5' */
```

**`asJsonValue`** - Convert a real to a JSON value
```walnut
Real->asJsonValue(Null => JsonValue)

3.14->asJsonValue;  /* 3.14 */
```

## 7. String Methods

### 7.1 Concatenation and Repetition
The result type of all methods in this section has a length range that is inferred from the input types (e.g. `String<3..5> + String<2..4> => String<5..9>`).

**`binaryPlus`** (`+`) - Concatenate two strings or a string-shaped value
```walnut
String->binaryPlus(Shape<String> => String)

'hello' + ' world';  /* 'hello world' */
```

**`concat`** - Concatenate two strings
```walnut
String->concat(String => String)

'hello'->concat(' world');  /* 'hello world' */
```

**`concatList`** - Concatenate a list of strings onto a string
```walnut
String->concatList(Array<String> => String)

'hello'->concatList[' ', 'world'];  /* 'hello world' */
```

**`binaryMultiply`** (`*`) - Repeat a string a given number of times
```walnut
String->binaryMultiply(Integer<0..> => String)

'ab' * 3;  /* 'ababab' */
```

### 7.2 Comparison

**`binaryGreaterThan`** (`>`) - Lexicographic greater-than comparison
```walnut
String->binaryGreaterThan(String => Boolean)

'b' > 'a';  /* true */
```

**`binaryGreaterThanEqual`** (`>=`) - Lexicographic greater-than-or-equal comparison
```walnut
String->binaryGreaterThanEqual(String => Boolean)

'a' >= 'a';  /* true */
```

**`binaryLessThan`** (`<`) - Lexicographic less-than comparison
```walnut
String->binaryLessThan(String => Boolean)

'a' < 'b';  /* true */
```

**`binaryLessThanEqual`** (`<=`) - Lexicographic less-than-or-equal comparison
```walnut
String->binaryLessThanEqual(String => Boolean)

'a' <= 'a';  /* true */
```

### 7.3 Length and Properties

**`length`** - Return the length of a string (in characters)
```walnut
String->length(Null => Integer)

'hello'->length;  /* 5 */
```
The return type preserves the string's length range: `String<3..10>` yields `Integer<3..10>`.

**`asBoolean`** - Test whether a string is non-empty
```walnut
String->asBoolean(Null => Boolean)

'hello'->asBoolean;  /* true */
''->asBoolean;        /* false */
```
If the string type has `minLength > 0`, the return type is narrowed to `True`. If `maxLength == 0`, the return type is narrowed to `False`.

### 7.4 Case Conversion
For both methods the return type preserves the same length range as the target.

**`toLowerCase`** - Convert all characters to lower case
```walnut
String->toLowerCase(Null => String)

'HELLO'->toLowerCase;  /* 'hello' */
```

**`toUpperCase`** - Convert all characters to upper case
```walnut
String->toUpperCase(Null => String)

'hello'->toUpperCase;  /* 'HELLO' */
```

### 7.5 Trimming
For all trim methods the return type has `minLength = 0` and `maxLength` from the target type.

**`trim`** - Remove whitespace (or specified characters) from both ends
```walnut
String->trim(Null => String)
String->trim(String => String)

'  hello  '->trim;        /* 'hello' */
'xxhelloxx'->trim('x');   /* 'hello' */
```

**`trimLeft`** - Remove whitespace (or specified characters) from the start
```walnut
String->trimLeft(Null => String)
String->trimLeft(String => String)

'  hello  '->trimLeft;       /* 'hello  ' */
'xxhelloxx'->trimLeft('x');  /* 'helloxx' */
```

**`trimRight`** - Remove whitespace (or specified characters) from the end
```walnut
String->trimRight(Null => String)
String->trimRight(String => String)

'  hello  '->trimRight;       /* '  hello' */
'xxhelloxx'->trimRight('x');  /* 'xxhello' */
```

### 7.6 Searching

**`contains`** - Check whether a string contains a substring
```walnut
String->contains(String => Boolean)

'hello world'->contains('world');  /* true */
```

**`startsWith`** - Check whether a string starts with a prefix
```walnut
String->startsWith(String => Boolean)

'hello'->startsWith('hel');  /* true */
```

**`endsWith`** - Check whether a string ends with a suffix
```walnut
String->endsWith(String => Boolean)

'hello'->endsWith('llo');  /* true */
```

**`positionOf`** - Find the first position of a substring
```walnut
String->positionOf(String => Result<Integer<0..>, SubstringNotInString>)

'hello'->positionOf('ll');  /* 2 */
'hello'->positionOf('x');   /* @SubstringNotInString */
```

**`lastPositionOf`** - Find the last position of a substring
```walnut
String->lastPositionOf(String => Result<Integer<0..>, SubstringNotInString>)

'hello hello'->lastPositionOf('hello');  /* 6 */
'hello'->lastPositionOf('x');            /* @SubstringNotInString */
```

### 7.7 Extraction

**`substring`** - Extract a substring by start position and length
```walnut
String->substring([start: Integer<0..>, length: Integer<0..>] => String)

'hello world'->substring[start: 6, length: 5];  /* 'world' */
```

**`substringRange`** - Extract a substring by start and end positions
```walnut
String->substringRange([start: Integer<0..>, end: Integer<0..>] => String)

'hello world'->substringRange[start: 0, end: 5];  /* 'hello' */
```

**`chunk`** - Split a string into fixed-size chunks
```walnut
String->chunk(Integer<1..> => Array<String>)

'abcdef'->chunk(2);  /* ['ab', 'cd', 'ef'] */
```
The array length and item string length are derived from the target length and chunk size.

**`binaryDivide`** (`/`) - Split a string into fixed-size chunks (alias for `chunk`)
```walnut
String->binaryDivide(Integer<1..> => Array<String>)

'abcdef' / 2;  /* ['ab', 'cd', 'ef'] */
```

**`split`** - Split a string by a delimiter
```walnut
String->split(String<1..> => Array<String>)

'a,b,c'->split(',');  /* ['a', 'b', 'c'] */
```
The array length and item string length are derived from the input paramaters.

**`binaryIntegerDivide`** (`//`) - Split into fixed-size chunks, discarding the incomplete trailing chunk
```walnut
String->binaryIntegerDivide(Integer<1..> => Array<String>)

'abcdefg' // 3;  /* ['abc', 'def'] */
```
The array length and item string length are derived from the input paramaters.

**`binaryModulo`** (`%`) - Return the trailing remainder after splitting into fixed-size chunks
```walnut
String->binaryModulo(Integer<1..> => String)

'abcdefg' % 3;  /* 'g' */
'abcdef' % 3;   /* '' */
```

### 7.8 Modification

**`binaryMinus`** (`-`) - Remove all occurrences of a substring (or list of substrings)
```walnut
String->binaryMinus(String<1..>|Array<String<1..>>|Set<String<1..>> => String)

'hello world' - 'o';             /* 'hell wrld' */
'hello world' - ['o', 'l'];      /* 'he wrd' */
```

Preserves StringSubsetType if applicable.

**`reverse`** - Reverse the characters of a string
```walnut
String->reverse(Null => String)

'hello'->reverse;  /* 'olleh' */
```
Return type preserves the same length range as the target.

**`unaryMinus`** (`-` prefix) - Reverse a string (alias for `reverse`)
```walnut
String->unaryMinus(Null => String)

-'hello';  /* 'olleh' */
```

**`replace`** - Replace occurrences of a match with a replacement string
```walnut
String->replace([match: String|RegExp, replacement: String] => String)

'hello world'->replace[match: 'world', replacement: 'there'];  /* 'hello there' */
```
The `match` field accepts either a plain `String` or a `RegExp` value.

### 7.9 Padding

**`padLeft`** - Pad a string on the left to a target length
```walnut
String->padLeft([length: Integer, padString: String] => String)

'42'->padLeft([length: 5, padString: '0']);  /* '00042' */
```

**`padRight`** - Pad a string on the right to a target length
```walnut
String->padRight([length: Integer, padString: String] => String)

'42'->padRight([length: 5, padString: '0']);  /* '42000' */
```

### 7.10 Pattern Matching

**`matchAgainstPattern`** - Match a string against a route-style pattern with `{param}` placeholders
```walnut
String->matchAgainstPattern(String => Map<String, String>|False)

'/users/42'->matchAgainstPattern('/users/{id}');
/* [id: '42'] */
'/other'->matchAgainstPattern('/users/{id}');
/* false */
```
Patterns use `{name}` placeholders that capture named segments. Returns a `Map` of captured values on match, or `False` on no match. When the pattern is a string subset type, the map's key type is narrowed to the specific placeholder names.

**`matchesRegexp`** - Test whether a string matches a regular expression pattern
```walnut
String->matchesRegexp(String => Boolean)

'hello123'->matchesRegexp('[0-9]+');  /* true */
```
The parameter is a regex pattern string (without delimiters).

### 7.11 HTML and Output

**`htmlEscape`** - Escape HTML special characters
```walnut
String->htmlEscape(Null => String)

'<b>bold</b>'->htmlEscape;  /* '&lt;b&gt;bold&lt;/b&gt;' */
```

**`OUT_HTML`** - Print the string to output as escaped HTML (with newlines as `<br>`)
```walnut
String->OUT_HTML(Null => String)

'hello\nworld'->OUT_HTML;  /* outputs escaped HTML, returns the original string */
```
Side effect: writes to standard output. Returns the original string unchanged.

**`OUT_TXT`** - Print the string to output as plain text
```walnut
String->OUT_TXT(Null => String)

'hello'->OUT_TXT;  /* outputs 'hello', returns the original string */
```
Side effect: writes to standard output. Returns the original string unchanged.

### 7.12 JSON

**`asJsonValue`** - Cast a string to a JsonValue
```walnut
String->asJsonValue(Null => JsonValue)

'hello'->asJsonValue;  /* JsonValue containing 'hello' */
```
Returns the string as a `JsonValue` type. The underlying value is unchanged.

**`jsonDecode`** - Parse a JSON string into a Walnut value
```walnut
String->jsonDecode(Null => Result<JsonValue, InvalidJsonString>)

'{'a': 1}'->jsonDecode;    /* [a: 1] */
'invalid'->jsonDecode;      /* @InvalidJsonString */
```
Returns a `Result` with the decoded `JsonValue` on success, or an `InvalidJsonString` error on parse failure.

### 7.13 Type Casts

**`asString`** - Identity cast (returns the string itself)
```walnut
String->asString(Null => String)

'hello'->asString;  /* 'hello' */
```
Return type preserves the exact type of the target.

**`asInteger`** - Parse a string as an integer
```walnut
String->asInteger(Null => Result<Integer, NotANumber>)

'42'->asInteger;      /* 42 */
'hello'->asInteger;   /* @NotANumber */
```
Returns a `Result` with the parsed `Integer` on success, or a `NotANumber` error if the string is not a valid integer representation.

**`asReal`** - Parse a string as a real number
```walnut
String->asReal(Null => Result<Real, NotANumber>)

'3.14'->asReal;      /* 3.14 */
'hello'->asReal;     /* @NotANumber */
```
Returns a `Result` with the parsed `Real` on success, or a `NotANumber` error if the string is not a valid numeric representation.

**`asBytes`** - Convert a string to its byte representation
```walnut
String->asBytes(Null => Bytes)

'hello'->asBytes;  /* Bytes value of 'hello' */
```

### 7.14 Constructors

**`asRegExp`** - Construct a RegExp from a string pattern
```walnut
String->asRegExp(Null => Result<RegExp, InvalidRegExp>)

'[0-9]+'->asRegExp;   /* RegExp value */
'[invalid'->asRegExp;  /* @InvalidRegExp */
```
If the string is a inferred to be a valid regular expression pattern, it returns a `RegExp` value.

**`asUuid`** - Construct a Uuid from a string
```walnut
String->asUuid(Null => Result<Uuid, InvalidUuid>)

'550e8400-e29b-41d4-a716-446655440000'->asUuid;  /* Uuid value */
'not-a-uuid'->asUuid;                              /* @InvalidUuid */
```
If the string is a valid UUID v4 format, it returns a `Uuid` value. 

## 8. Array Methods

### 8.1 Properties

**`length`** - Returns the number of elements in the array
```walnut
Array<T>->length(Null => Integer)

[3, 1, 4]->length;  /* 3 */
```
The length range is bound to the array's `minLength` and `maxLength`.

**`item`** - Returns the element at the given index
```walnut
Array<T>->item(Integer => T | Result<T, IndexOutOfRange>)

[10, 20, 30]->item(1);  /* 20 */
[10, 20, 30]->item(5);  /* @IndexOutOfRange[index: 5] */
```
When the index is guaranteed to be within bounds at compile time, the return type is `T` directly.

**`first`** - Returns the first element of the array
```walnut
Array<T>->first(Null => T | Result<T, ItemNotFound>)

[10, 20, 30]->first;  /* 10 */
```
Returns `T` directly when the array is guaranteed non-empty.

**`last`** - Returns the last element of the array
```walnut
Array<T>->last(Null => T | Result<T, ItemNotFound>)

[10, 20, 30]->last;  /* 30 */
```
Returns `T` directly when the array is guaranteed non-empty.

**`contains`** - Checks whether the array contains a given value
```walnut
Array<T>->contains(Any => Boolean)

[1, 2, 3]->contains(2);  /* true */
[1, 2, 3]->contains(5);  /* false */
```

### 8.2 Searching

**`indexOf`** - Returns the index of the first occurrence of a value
```walnut
Array<T>->indexOf(Any => Result<Integer<0..>, ItemNotFound>)

[10, 20, 30]->indexOf(20);  /* 1 */
[10, 20, 30]->indexOf(99);  /* @ItemNotFound */
```

**`lastIndexOf`** - Returns the index of the last occurrence of a value
```walnut
Array<T>->lastIndexOf(Any => Result<Integer<0..>, ItemNotFound>)

[10, 20, 10]->lastIndexOf(10);  /* 2 */
[10, 20, 30]->lastIndexOf(99);  /* @ItemNotFound */
```

**`findFirst`** - Returns the first element matching a predicate
```walnut
Array<T>->findFirst((^T => Boolean) => Result<T, ItemNotFound>)

[1, 2, 3, 4]->findFirst(^n: Integer => Boolean :: n > 2);  /* 3 */
```

**`findLast`** - Returns the last element matching a predicate
```walnut
Array<T>->findLast((^T => Boolean) => Result<T, ItemNotFound>)

[1, 2, 3, 4]->findLast(^n: Integer => Boolean :: n > 2);  /* 4 */
```

### 8.3 Adding Elements

**`binaryPlus`** (`+`) - Concatenates two arrays
```walnut
Array<T>->binaryPlus(Array<U> => Array<T|U>)

[1, 2] + [3, 4];  /* [1, 2, 3, 4] */
```

**`insertFirst`** - Inserts an element at the beginning
```walnut
Array<T>->insertFirst(U => Array<T|U>)

[2, 3]->insertFirst(1);  /* [1, 2, 3] */
```

**`insertLast`** - Inserts an element at the end
```walnut
Array<T>->insertLast(U => Array<T|U>)

[1, 2]->insertLast(3);  /* [1, 2, 3] */
```

**`insertAt`** - Inserts an element at a specific index
```walnut
Array<T>->insertAt([value: U, index: Integer<0..>] => Array<T|U> | Result<Array<T|U>, IndexOutOfRange>)

[1, 3, 4]->insertAt([value: 2, index: 1]);  /* [1, 2, 3, 4] */
```
Returns `Result<Array<T|U>, IndexOutOfRange>` when the index might be out of bounds.

**`appendWith`** - Appends all elements from another array (alias of `+`)
```walnut
Array<T>->appendWith(Array<U> => Array<T|U>)

[1, 2]->appendWith([3, 4]);  /* [1, 2, 3, 4] */
```

### 8.4 Removing Elements

**`without`** - Removes the first occurrence of a value
```walnut
Array<T>->without(Any => Result<Array<T>, ItemNotFound>)

[1, 2, 3, 2]->without(2);  /* [1, 3, 2] */
[1, 2, 3]->without(5);     /* @ItemNotFound */
```

**`withoutAll`** - Removes all occurrences of a value
```walnut
Array<T>->withoutAll(Any => Array<T>)

[1, 2, 3, 2]->withoutAll(2);  /* [1, 3] */
```

**`withoutByIndex`** - Removes the element at a specific index, returning both the removed element and the remaining array
```walnut
Array<T>->withoutByIndex(Integer => [element: T, array: Array<T>] | Result<[element: T, array: Array<T>], IndexOutOfRange>)

[10, 20, 30]->withoutByIndex(1);  /* [element: 20, array: [10, 30]] */
```
Returns `Result` when the index might be out of bounds. With a known tuple type and literal index, the types of `element` and `array` are precisely narrowed.

**`withoutFirst`** - Removes and returns the first element
```walnut
Array<T>->withoutFirst(Null => [element: T, array: Array<T>] | Result<[element: T, array: Array<T>], ItemNotFound>)

[10, 20, 30]->withoutFirst;  /* [element: 10, array: [20, 30]] */
```
Returns the record directly when the array is guaranteed non-empty. Returns `Result` when the array might be empty.

**`withoutLast`** - Removes and returns the last element
```walnut
Array<T>->withoutLast(Null => [element: T, array: Array<T>] | Result<[element: T, array: Array<T>], ItemNotFound>)

[10, 20, 30]->withoutLast;  /* [element: 30, array: [10, 20]] */
```
Returns the record directly when the array is guaranteed non-empty. Returns `Result` when the array might be empty.

### 8.5 Transformation

**`map`** - Transforms each element using a function
```walnut
Array<T>->map((^T => R) => Array<R>)

[1, 2, 3]->map(^n: Integer => Integer :: n * 2);  /* [2, 4, 6] */
```
If the callback returns `Result<R, E>`, the overall return type is `Result<Array<R>, E>`. Execution short-circuits on the first error.

**`mapIndexValue`** - Transforms each element using a function that receives both the index and value
```walnut
Array<T>->mapIndexValue((^[index: Integer, value: T] => R) => Array<R>)

['a', 'b']->mapIndexValue(^[index: Integer, value: String] => String :: {#index->asString; #value});
/* ['0a', '1b'] */
```
If the callback returns `Result<R, E>`, the overall return type is `Result<Array<R>, E>`.

**`flatMap`** - Maps each element to an array, then flattens the result
```walnut
Array<T>->flatMap((^T => Array<R>) => Array<R>)

[[1, 2], [3, 4]]->flatMap(^a: Array<Integer> => Array<Integer> :: a);  /* [1, 2, 3, 4] */
```
The callback must return an array type. If the callback returns `Result<Array<R>, E>`, the overall return type is `Result<Array<R>, E>`.

**`filter`** - Keeps only elements matching a predicate
```walnut
Array<T>->filter((^T => Boolean) => Array<T>)

[1, 2, 3, 4]->filter(^n: Integer => Boolean :: n > 2);  /* [3, 4] */
```
The minimum length of the result is always 0. If the callback returns `Result<Boolean, E>`, the overall return type is `Result<Array<T>, E>`.

**`reverse`** - Reverses the order of elements
```walnut
Array<T>->reverse(Null => Array<T>)

[1, 2, 3]->reverse;  /* [3, 2, 1] */
```

**`sort`** - Sorts elements in ascending order (strings alphabetically, numbers numerically)
```walnut
Array<T: String|Integer|Real>->sort((Null | [reverse: Boolean]) => Array<T>)

[3, 1, 2]->sort;                      /* [1, 2, 3] */
[3, 1, 2]->sort([reverse: true]);     /* [3, 2, 1] */
```

**`shuffle`** - Randomly reorders the elements
```walnut
Array<T>->shuffle(Null => Array<T>)

[1, 2, 3]->shuffle;  /* [2, 3, 1] (random order) */
```

**`unique`** - Removes duplicate values, preserving order
```walnut
Array<T: String|Integer|Real>->unique(Null => Array<T>)

[1, 2, 2, 3, 1]->unique;  /* [1, 2, 3] */
```

**`uniqueSet`** - Removes duplicate values and returns a Set
```walnut
Array<T: String|Integer|Real>->uniqueSet(Null => Set<T>)

[1, 2, 2, 3, 1]->uniqueSet;  /* [1; 2; 3] */
```

**`flatten`** - Flattens an array of arrays into a single array
```walnut
Array<Array<T>>->flatten(Null => Array<T>)

[[1, 2], [3, 4]]->flatten;  /* [1, 2, 3, 4] */
```

**`chainInvoke`** - Pipes a value through an array of functions
```walnut
Array<^T => S>->chainInvoke(T => S), where S is a subtype of T

fns = [^n: Integer => Integer :: n + 1, ^n: Integer => Integer :: n * 2];
fns->chainInvoke(3);  /* 8 */
```
Requires each function's return type to be a subtype of its parameter type, ensuring composability.

### 8.6 Slicing and Padding

**`slice`** - Extracts a portion of the array by start position and optional length
```walnut
Array<T>->slice([start: Integer<0..>, length?: Integer<0..>] => Array<T>)

[10, 20, 30, 40]->slice[start: 1, length: 2];  /* [20, 30] */
[10, 20, 30, 40]->slice[start: 1];              /* [20, 30, 40] */
```

**`sliceRange`** - Extracts a portion of the array by start and end indices
```walnut
Array<T>->sliceRange([start: Integer<0..>, end: Integer<0..>] => Array<T>)

[10, 20, 30, 40]->sliceRange[start: 1, end: 3];  /* [20, 30] */
```
The `end` index is exclusive.

**`take`** - Takes the first N elements
```walnut
Array<T>->take(Integer<0..> => Array<T>)

[10, 20, 30, 40]->take(2);  /* [10, 20] */
```

**`drop`** - Drops the first N elements
```walnut
Array<T>->drop(Integer<0..> => Array<T>)

[10, 20, 30, 40]->drop(2);  /* [30, 40] */
```

**`chunk`** - Splits the array into chunks of a given size
```walnut
Array<T>->chunk(Integer<1..> => Array<Array<T>>)

[1, 2, 3, 4, 5]->chunk(2);  /* [[1, 2], [3, 4], [5]] */
```

**`padLeft`** - Pads the array on the left to a specified length
```walnut
Array<T>->padLeft([length: Integer, value: U] => Array<T|U>)

[1, 2, 3]->padLeft[length: 5, value: 0];  /* [0, 0, 1, 2, 3] */
```

**`padRight`** - Pads the array on the right to a specified length
```walnut
Array<T>->padRight([length: Integer, value: U] => Array<T|U>)

[1, 2, 3]->padRight[length: 5, value: 0];  /* [1, 2, 3, 0, 0] */
```

### 8.7 Aggregation

**`min`** - Returns the minimum value in a non-empty numeric array
```walnut
Array<Integer|Real, 1..>->min(Null => Integer|Real)

[3, 1, 4, 1, 5]->min;  /* 1 */
```

**`max`** - Returns the maximum value in a non-empty numeric array
```walnut
Array<Integer|Real, 1..>->max(Null => Integer|Real)

[3, 1, 4, 1, 5]->max;  /* 5 */
```

**`sum`** - Returns the sum of all numeric elements
```walnut
Array<Integer|Real>->sum(Null => Integer|Real)

[1, 2, 3]->sum;  /* 6 */
```

**`product`** - Returns the product of all numeric elements
```walnut
Array<Integer|Real>->product(Null => Integer|Real)

[2, 3, 4]->product;  /* 24 */
```

**`reduce`** - Reduces the array to a single value using a reducer function and initial value
```walnut
Array<T>->reduce(([reducer: ^[result: R, item: T] => R, initial: R]) => R)

[1, 2, 3]->reduce([
    reducer: ^[result: Integer, item: Integer] => Integer :: #result + #item,
    initial: 0
]);  /* 6 */
```
If the reducer returns `Result<R, E>`, the overall return type is `Result<R, E>`. Execution short-circuits on the first error.

**`countValues`** - Counts the occurrences of each value, returning a Map
```walnut
Array<String|Integer>->countValues(Null => Map<String|Integer>)

['a', 'b', 'a', 'c']->countValues;  /* [a: 2, b: 1, c: 1] */
```
Requires items to be `String` or `Integer`. Keys in the returned Map match the item values.

**`all`** - Returns true if all elements satisfy a predicate
```walnut
Array<T>->all((^T => Boolean) => Boolean)

[2, 4, 6]->all(^n: Integer => Boolean :: n > 0);  /* true */
```

**`any`** - Returns true if any element satisfies a predicate
```walnut
Array<T>->any((^T => Boolean) => Boolean)

[1, 2, 3]->any(^n: Integer => Boolean :: n > 2);  /* true */
```

### 8.8 Conversion

**`combineAsString`** - Joins string elements with a separator
```walnut
Array<String>->combineAsString(String => String)

['hello', 'world']->combineAsString(', ');  /* 'hello, world' */
```

**`flip`** - Converts an array of strings into a Map with values as keys and indices as values
```walnut
Array<K>->flip(Null => Map<K:Integer<0..>>), where K is a subtype of String

['a', 'b', 'c']->flip;  /* [a: 0, b: 1, c: 2] */
```
Duplicate values will be overwritten (last occurrence wins).

**`flipMap`** - Converts an array of strings into a Map using a callback to produce values
```walnut
Array<String>->flipMap((^String => R) => Map<R>)

['a', 'b']->flipMap(^s: String => String :: s->uppercase);  /* [a: 'A', b: 'B'] */
```
If the callback returns `Result<R, E>`, the overall return type is `Result<Map<R>, E>`.

**`zip`** - Pairs elements from two arrays into an array of tuples
```walnut
Array<T>->zip(Array<U> => Array<[T, U]>)

[1, 2, 3]->zip(['a', 'b', 'c']);  /* [[1, 'a'], [2, 'b'], [3, 'c']] */
```
Result length is the minimum of the two input lengths.

**`zipMap`** - Zips an array of string keys with an array of values into a Map
```walnut
Array<String>->zipMap(Array<V> => Map<V>)

['a', 'b']->zipMap([1, 2]);  /* [a: 1, b: 2] */
```
Result length is the minimum of the two input lengths.

**`indexBy`** - Groups items into a Map using a key function (last value per key wins)
```walnut
Array<T>->indexBy((^T => String) => Map<T>)

users->indexBy(^u: User => String :: u.id->asString);
/* [1: User(...), 2: User(...)] */
```
Duplicate keys will be overwritten.

**`groupBy`** - Groups items into a Map of arrays using a key function
```walnut
Array<T>->groupBy((^T => String) => Map<Array<T>>)

[1, 2, 3, 4]->groupBy(^n: Integer => String :: ?whenTrue(n > 2) {'big'} ?? {'small'});
/* [small: [1, 2], big: [3, 4]] */
```

**`format`** - Formats array elements into a string template using `{0}`, `{1}`, etc. as placeholders
```walnut
Array<Shape<String>>->format(String => String | Result<String, CannotFormatString>)

['John', 30]->format('{0} is {1} years old');  /* 'John is 30 years old' */
```
Requires items to be castable to `String` (i.e., subtypes of `Shape<String>`). Returns `Result` when a placeholder index might not exist in the array.

**`asBoolean`** - Returns true if the array is non-empty, false if empty
```walnut
Array<T>->asBoolean(Null => Boolean)

[1, 2, 3]->asBoolean;  /* true */
[]->asBoolean;          /* false */
```
Returns `True` when the array is guaranteed non-empty, `False` when guaranteed empty, `Boolean` otherwise.

**`asJsonValue`** - Converts the array to a JSON-compatible value
```walnut
Array<T>->asJsonValue(Null => JsonValue)

[1, 'two', 3]->asJsonValue;
```
Requires each item type to support `asJsonValue`.

### 8.9 Operators

**`binaryMinus`** (`-`) - Removes all occurrences of a value (alias for `withoutAll`)
```walnut
Array<T>->binaryMinus(Any => Array<T>)

[1, 2, 3, 2] - 2;  /* [1, 3] */
```

**`binaryMultiply`** (`*`) - Repeats the array N times
```walnut
Array<T>->binaryMultiply(Integer<0..> => Array<T>)

[1, 2] * 3;  /* [1, 2, 1, 2, 1, 2] */
```

**`binaryDivide`** (`/`) - Splits the array into chunks of size N (same as `chunk`)
```walnut
Array<T>->binaryDivide(Integer<1..> => Array<Array<T>>)

[1, 2, 3, 4, 5] / 2;  /* [[1, 2], [3, 4], [5]] */
```

**`binaryIntegerDivide`** (`//`) - Splits into chunks of size N, discarding incomplete final chunk
```walnut
Array<T>->binaryIntegerDivide(Integer<1..> => Array<Array<T>>)

[1, 2, 3, 4, 5] // 2;  /* [[1, 2], [3, 4]] */
```

**`binaryModulo`** (`%`) - Returns the remainder elements after chunking by N
```walnut
Array<T>->binaryModulo(Integer<1..> => Array<T>)

[1, 2, 3, 4, 5] % 2;  /* [5] */
[1, 2, 3, 4] % 2;      /* [] */
```
Returns elements that would form the incomplete last chunk when dividing by N. If all chunks are complete, returns an empty array.

**`unaryMinus`** (`-`) - Reverses the array (alias for `reverse`)
```walnut
Array<T>->unaryMinus(Null => Array<T>)

-[1, 2, 3];  /* [3, 2, 1] */
```

### 8.10 Partition

**`partition`** - Splits the array into two groups based on a predicate
```walnut
Array<T>->partition((^T => Boolean) => [matching: Array<T>, notMatching: Array<T>])

[1, 2, 3, 4]->partition(^n: Integer => Boolean :: n > 2);
/* [matching: [3, 4], notMatching: [1, 2]] */
```
If the callback returns `Result<Boolean, E>`, the overall return type is `Result<[matching: Array<T>, notMatching: Array<T>], E>`. Execution short-circuits on the first error.

## 9. Map Methods

A `Map<T>` is a collection of key-value pairs where keys are strings and values are of type `T`. Map literals use the record syntax: `[a: 1, b: 2, c: 3]`.

### 9.1 Properties

**`length`** - Returns the number of entries in the map
```walnut
Map<T>->length(Null => Integer)

[a: 1, b: 2, c: 3]->length;  /* 3 */
```

**`keys`** - Returns the keys as an array of strings
```walnut
Map<K:T>->keys(Null => Array<K>), where K is a subtype of String

[a: 1, b: 2]->keys;  /* ['a', 'b'] */
```

**`keysSet`** - Returns the keys as a set of strings
```walnut
Map<K:T>->keysSet(Null => Set<K>), where K is a subtype of String

[a: 1, b: 2]->keysSet;  /* ['a'; 'b'] */
```

**`values`** - Returns the values as an array
```walnut
Map<T>->values(Null => Array<T>)

[a: 1, b: 2, c: 3]->values;  /* [1, 2, 3] */
```

**`item`** - Accesses a value by its string key. Returns the value directly if the key is known to exist at compile time, or a `Result` if the key might not exist
```walnut
Map<T>->item(String => Result<T, MapItemNotFound>)

[a: 1, b: 2]->item('a');  /* 1 */
[a: 1, b: 2]->item('z');  /* @MapItemNotFound[key: 'z'] */
```

**`contains`** - Checks whether the map contains a given value
```walnut
Map<T>->contains(T => Boolean)

[a: 1, b: 2, c: 3]->contains(2);  /* true */
[a: 1, b: 2, c: 3]->contains(5);  /* false */
```

**`keyExists`** - Checks whether a key exists in the map
```walnut
Map<T>->keyExists(String => Boolean)

[a: 1, b: 2]->keyExists('a');  /* true */
[a: 1, b: 2]->keyExists('z');  /* false */
```

### 9.2 Searching

**`keyOf`** - Finds the key of the first occurrence of a value. Returns a `Result` with the key string or `ItemNotFound`
```walnut
Map<T>->keyOf(T => Result<String, ItemNotFound>)

[a: 1, b: 2, c: 3]->keyOf(2);  /* 'b' */
[a: 1, b: 2, c: 3]->keyOf(5);  /* @ItemNotFound */
```

**`findFirst`** - Finds the first value matching a predicate. Returns a `Result` with the value or `ItemNotFound`
```walnut
Map<T>->findFirst((^T => Boolean) => Result<T, ItemNotFound>)

[a: 1, b: 5, c: 3]->findFirst(^v: Integer => Boolean :: v > 4);  /* 5 */
[a: 1, b: 2, c: 3]->findFirst(^v: Integer => Boolean :: v > 10);  /* @ItemNotFound */
```

**`findFirstKeyValue`** - Finds the first key-value pair matching a predicate. The callback receives a `[key: String, value: T]` record. Returns a `Result` with the matching record or `ItemNotFound`
```walnut
Map<T>->findFirstKeyValue((^[key: String, value: T] => Boolean) => Result<[key: String, value: T], ItemNotFound>)

[a: 1, b: 5, c: 3]->findFirstKeyValue(
    ^kv: [key: String, value: Integer] => Boolean :: kv.value > 4
);  /* [key: 'b', value: 5] */
```

### 9.3 Modification

**`mergeWith`** - Merges with another map. Entries from the parameter map overwrite those with matching keys. Behaves like `+` but as a named method
```walnut
Map<K:T>->mergeWith(Map<L:U> => Map<K|L:T|U>), K and L are a subtypes of String

[a: 1, b: 2]->mergeWith([c: 3]);  /* [a: 1, b: 2, c: 3] */
```

**`binaryPlus`** (`+` operator) - alias of `mergeWith`
```walnut
Map<K:T>->binaryPlus(Map<L:U> => Map<K|L:T|U>), K and L are a subtypes of String

[a: 1, b: 2] + [c: 3, d: 4];  /* [a: 1, b: 2, c: 3, d: 4] */
[a: 1, b: 2] + [b: 9, c: 3];  /* [a: 1, b: 9, c: 3] */
```

**`withKeyValue`** - Returns a new map with an added or updated key-value pair
```walnut
Map<K:T>->withKeyValue([key: L, value: U] => Map<K|L:T|U>), K and L are a subtypes of String

[a: 1, b: 2]->withKeyValue([key: 'c', value: 3]);  /* [a: 1, b: 2, c: 3] */
[a: 1, b: 2]->withKeyValue([key: 'b', value: 9]);  /* [a: 1, b: 9] */
```

**`without`** - Removes the first occurrence of a value. Returns a `Result` with the new map or `ItemNotFound` if the value is not present
```walnut
Map<K:T>->without(Any => Result<Map<K:T>, ItemNotFound>)

[a: 1, b: 2, c: 3]->without(2);  /* [a: 1, c: 3] */
[a: 1, b: 2, c: 3]->without(5);  /* @ItemNotFound */
```

**`withoutAll`** - Removes all occurrences of a value from the map
```walnut
Map<K:T>->withoutAll(Any => Map<K:T>)

[a: 1, b: 2, c: 1]->withoutAll(1);  /* [b: 2] */
```

**`withoutByKey`** - Removes an entry by key. Returns the removed element and the remaining map. Returns a `Result` if the key might not exist
```walnut
Map<K:T>->withoutByKey(String => Result<[element: T, map: Map<K:T>], MapItemNotFound>)

[a: 1, b: 2, c: 3]->withoutByKey('b');  /* [element: 2, map: [a: 1, c: 3]] */
[a: 1, b: 2]->withoutByKey('z');        /* @MapItemNotFound[key: 'z'] */
```

**`valuesWithoutKey`** - Removes a key and returns only the remaining map (without the removed element). Returns a `Result` if the key might not exist
```walnut
Map<K:T>->valuesWithoutKey(String => Result<Map<K:T>, MapItemNotFound>)

[a: 1, b: 2, c: 3]->valuesWithoutKey('b');  /* [a: 1, c: 3] */
[a: 1, b: 2]->valuesWithoutKey('z');         /* @MapItemNotFound[key: 'z'] */
```

**`remapKeys`** - Transforms the keys of a map using a callback function. The callback receives each key string and must return a new string key. If the callback returns a `Result`, errors are propagated
```walnut
Map<K:T>->remapKeys((^K => L) => Map<L:T>), where K and L as subtypes of String

[a: 1, b: 2]->remapKeys(^k: String => String :: 'prefix_' + k);
/* [prefix_a: 1, prefix_b: 2] */
```

### 9.4 Transformation

**`map`** - Transforms each value using a callback. If the callback returns a `Result`, errors are propagated
```walnut
Map<K:T>->map((^T => U) => Map<K:U>), K is a subtype of String

[a: 1, b: 2, c: 3]->map(^v: Integer => Integer :: v * 2);  /* [a: 2, b: 4, c: 6] */
```

**`mapKeyValue`** - Transforms each value using a callback that receives both the key and value as a `[key: String, value: T]` record. If the callback returns a `Result`, errors are propagated
```walnut
Map<K:T>->mapKeyValue((^[key: K, value: T] => U) => Map<K:U>), K is a subtype of String

[a: 1, b: 2]->mapKeyValue(
    ^kv: [key: String, value: Integer] => String :: kv.key + '=' + kv.value->asString
);  /* [a: 'a=1', b: 'b=2'] */
```

**`filter`** - Returns a new map containing only entries whose values satisfy a predicate. If the callback returns a `Result<Boolean, E>`, errors are propagated
```walnut
Map<K:T>->filter((^T => Boolean) => Map<K:T>)

[a: 1, b: 5, c: 3]->filter(^v: Integer => Boolean :: v > 2);  /* [b: 5, c: 3] */
```

**`filterKeyValue`** - Filters entries using a callback that receives `[key: String, value: T]` records
```walnut
Map<K:T>->filterKeyValue((^[key: K, value: T] => Boolean) => Map<K:T>), K is a subtype of String

[a: 1, b: 5, c: 3]->filterKeyValue(
    ^kv: [key: String, value: Integer] => Boolean :: kv.key != 'a'
);  /* [b: 5, c: 3] */
```

**`flip`** - Swaps keys and values. Only works on maps whose values are strings (`Map<String>`)
```walnut
Map<K:L>->flip(Null => Map<L:K>), K and L are subtypes of String

[a: 'x', b: 'y']->flip;  /* [x: 'a', y: 'b'] */
```

**`format`** - Formats a template string by replacing `{key}` placeholders with the corresponding map values (cast to strings). Returns a `Result` if the key might not exist in the map
```walnut
Map<K:T>->format(String => Result<String, CannotFormatString>)

[name: 'Alice', age: 30]->format('Hello, {name}! Age: {age}');
/* 'Hello, Alice! Age: 30' */
```

**`partition`** - Splits the map into two maps based on a predicate
```walnut
Map<K:T>->partition((^T => Boolean) => [matching: Map<K:T>, notMatching: Map<K:T>])

[a: 1, b: 5, c: 3]->partition(^v: Integer => Boolean :: v > 2);
/* [matching: [b: 5, c: 3], notMatching: [a: 1]] */
```

**`sort`** - Sorts the map by its values (preserving keys). 
```walnut
Map<K:T>->sort((Null|[reverse: Boolean]) => Map<K:T>), where T is one of String, Integer, or Real

[a: 3, b: 1, c: 2]->sort(null);               /* [b: 1, c: 2, a: 3] */
[a: 3, b: 1, c: 2]->sort([reverse: true]);     /* [a: 3, c: 2, b: 1] */
```

**`keySort`** - Sorts the map by its keys (alphabetically). Accepts `null` or `[reverse: Boolean]` as a parameter
```walnut
Map<K:T>->keySort((Null|[reverse: Boolean]) => Map<K:T>)

[c: 3, a: 1, b: 2]->keySort(null);             /* [a: 1, b: 2, c: 3] */
[c: 3, a: 1, b: 2]->keySort([reverse: true]);  /* [c: 3, b: 2, a: 1] */
```

**`zip`** - Combines two maps by matching keys. For each shared key, produces a tuple of the two values
```walnut
Map<K:T>->zip(Map<L:U> => Map<K&L:[T, U]>)

[a: 1, b: 2, c: 3]->zip([a: 'x', b: 'y']);  /* [a: [1, 'x'], b: [2, 'y']] */
```

### 9.5 Conversion

**`asBoolean`** - Returns `true` if the map is non-empty, `false` if empty
```walnut
Map<T>->asBoolean(Null => Boolean)

[a: 1, b: 2]->asBoolean;  /* true */
[:]->asBoolean;            /* false */
```

**`asJsonValue`** - Converts the map to a JSON-compatible value. Recursively calls `asJsonValue` on each value
```walnut
Map<T>->asJsonValue(Null => JsonValue)
```

**`asArray`** - Returns the map values as an array (discarding keys). Alias for `values`
```walnut
Map<T>->asArray(Null => Array<T>)

[a: 1, b: 2, c: 3]->asArray;  /* [1, 2, 3] */
```

---

## 10. Set Methods

A `Set<T>` is an ordered collection of unique values of type `T`. Set literals use the semicolon syntax: `[1; 2; 3]`.

### 10.1 Properties

**`length`** - Returns the number of elements in the set
```walnut
Set<T>->length(Null => Integer)

[1; 2; 3]->length;  /* 3 */
```

**`values`** - Returns the elements as an array
```walnut
Set<T>->values(Null => Array<T>)

[1; 2; 3]->values;  /* [1, 2, 3] */
```

**`contains`** - Checks whether the set contains a given value. Returns `false` at compile time if the parameter type cannot match the item type
```walnut
Set<T>->contains(T => Boolean)

[1; 2; 3]->contains(2);  /* true */
[1; 2; 3]->contains(5);  /* false */
```

### 10.2 Modification

**`insert`** - Returns a new set with the given element added. If the element already exists, the set is unchanged
```walnut
Set<T>->insert(U => Set<T|U>)

[1; 2; 3]->insert(4);  /* [1; 2; 3; 4] */
[1; 2; 3]->insert(2);  /* [1; 2; 3] */
```

**`without`** - Returns a new set with the given element removed. If the element does not exist, the set is unchanged
```walnut
Set<T>->without(T => Set<T>)

[1; 2; 3]->without(2);  /* [1; 3] */
[1; 2; 3]->without(5);  /* [1; 2; 3] */
```

**`withRemoved`** - Removes a new set with the given element removed. Returns `ItemNotFound` if the element is not present
```walnut
Set<T>->withRemoved(T => Result<Set<T>, ItemNotFound>)

[1; 2; 3]->withRemoved(2);  /* [1; 3] */
[1; 2; 3]->withRemoved(5);  /* @ItemNotFound */
```

### 10.3 Set Operations

**`binaryBitwiseAnd`** (`&` operator) - Intersection. Returns a set of elements present in both sets
```walnut
Set<T>->binaryBitwiseAnd(Set<U> => Set<T&U>)

[1; 2; 3] & [2; 3; 4];  /* [2; 3] */
[1; 2; 3] & [4; 5; 6];  /* [] */
```

**`binaryBitwiseXor`** (`^` operator) - Symmetric difference. Returns elements that are in either set but not in both
```walnut
Set<T>->binaryBitwiseXor(Set<U> => Set<T|U>)

[1; 2; 3] ^ [2; 3; 4];  /* [1; 4] */
```

**`binaryPlus`** (`+` operator) - Union. Returns a set containing all elements from both sets
```walnut
Set<T>->binaryPlus(Set<U> => Set<T|U>)

[1; 2; 3] + [3; 4; 5];  /* [1; 2; 3; 4; 5] */
```

**`binaryMinus`** (`-` operator) - Difference. Returns elements in the target set that are not in the parameter set
```walnut
Set<T>->binaryMinus(Set<U> => Set<T>)

[1; 2; 3; 4] - [2; 4];  /* [1; 3] */
```

**`binaryMultiply`** (`*` operator) - Cartesian product. Returns a set of tuples pairing each element from the first set with each element from the second
```walnut
Set<T>->binaryMultiply(Set<U> => Set<[T, U]>)

[1; 2] * ['a'; 'b'];  /* [[1, 'a']; [1, 'b']; [2, 'a']; [2, 'b']] */
```

### 10.4 Set Comparisons

**`binaryGreaterThan`** (`>` operator) - Proper superset test. Returns `true` if the target is a strict superset of the parameter
```walnut
Set<T>->binaryGreaterThan(Set<U> => Boolean)

[1; 2; 3] > [1; 2];     /* true */
[1; 2; 3] > [1; 2; 3];  /* false */
```

**`binaryGreaterThanEqual`** (`>=` operator) - Superset test. Returns `true` if the target is a superset of (or equal to) the parameter
```walnut
Set<T>->binaryGreaterThanEqual(Set<U> => Boolean)

[1; 2; 3] >= [1; 2];     /* true */
[1; 2; 3] >= [1; 2; 3];  /* true */
```

**`binaryLessThan`** (`<` operator) - Proper subset test. Returns `true` if the target is a strict subset of the parameter
```walnut
Set<T>->binaryLessThan(Set<U> => Boolean)

[1; 2] < [1; 2; 3];  /* true */
[1; 2] < [1; 2];     /* false */
```

**`binaryLessThanEqual`** (`<=` operator) - Subset test. Returns `true` if the target is a subset of (or equal to) the parameter
```walnut
Set<T>->binaryLessThanEqual(Set<U> => Boolean)

[1; 2] <= [1; 2; 3];  /* true */
[1; 2] <= [1; 2];     /* true */
```

**`isDisjointWith`** - Returns `true` if the two sets have no elements in common
```walnut
Set<T>->isDisjointWith(Set<U> => Boolean)

[1; 2; 3]->isDisjointWith([4; 5; 6]);  /* true */
[1; 2; 3]->isDisjointWith([3; 4; 5]);  /* false */
```

**`isSubsetOf`** - Returns `true` if the target set is a subset of (or equal to) the parameter set
```walnut
Set<T>->isSubsetOf(Set<U> => Boolean)

[1; 2]->isSubsetOf([1; 2; 3]);  /* true */
[1; 4]->isSubsetOf([1; 2; 3]);  /* false */
```

**`isSupersetOf`** - Returns `true` if the target set is a superset of (or equal to) the parameter set
```walnut
Set<T>->isSupersetOf(Set<T> => Boolean)

[1; 2; 3]->isSupersetOf([1; 2]);  /* true */
[1; 2]->isSupersetOf([1; 2; 3]);  /* false */
```

### 10.5 Transformation

**`map`** - Transforms each element using a callback. If the callback returns a `Result`, errors are propagated. The resulting set may have fewer elements due to deduplication
```walnut
Set<T>->map((^T => U) => Set<U>)

[1; 2; 3]->map(^v: Integer => Integer :: v * 2);  /* [2; 4; 6] */
```

**`filter`** - Returns a new set containing only elements that satisfy a predicate. If the callback returns a `Result<Boolean, E>`, errors are propagated
```walnut
Set<T>->filter((^T => Boolean) => Set<T>)

[1; 5; 3; 8]->filter(^v: Integer => Boolean :: v > 3);  /* [5; 8] */
```

**`flip`** - Converts a `Set<String>` into a `Map<Integer>` where set values become map keys and map values are the original positional indices
```walnut
Set<K>->flip(Null => Map<K:Integer<0..>>), where K is a subtype of String

['a'; 'b'; 'c']->flip;  /* [a: 0, b: 1, c: 2] */
```

**`flipMap`** - Converts a `Set<String>` into a `Map` by using set values as keys and mapping each through a callback to produce the values. If the callback returns a `Result`, errors are propagated
```walnut
Set<K>->flipMap((^K => U) => Map<K:U>)

['a'; 'b']->flipMap(^k: String => Integer :: k->length);  /* [a: 1, b: 1] */
```

**`zipMap`** - Combines a `Set<String>` with an array, pairing each set element (as a key) with the corresponding array element (by position). The result is a map
```walnut
Set<String>->zipMap(Array<U> => Map<U>)

['a'; 'b'; 'c']->zipMap([1, 2, 3]);  /* [a: 1, b: 2, c: 3] */
['a'; 'b']->zipMap([1, 2, 3]);       /* [a: 1, b: 2] */
```

**`partition`** - Splits the set into two sets based on a predicate
```walnut
Set<T>->partition((^T => Boolean) => [matching: Set<T>, notMatching: Set<T>])

[1; 5; 3; 8]->partition(^v: Integer => Boolean :: v > 3);
/* [matching: [5; 8], notMatching: [1; 3]] */
```

**`sort`** - Sorts the set elements. Works on sets with string or numeric values. Accepts `null` or `[reverse: Boolean]` as a parameter
```walnut
Set<T>->sort(Null|[reverse: Boolean] => Set<T>)

[3; 1; 2]->sort(null);               /* [1; 2; 3] */
[3; 1; 2]->sort([reverse: true]);    /* [3; 2; 1] */
```

### 10.6 Conversion

**`asBoolean`** - Returns `true` if the set is non-empty, `false` if empty
```walnut
Set<T>->asBoolean(Null => Boolean)

[1; 2; 3]->asBoolean;  /* true */
[;]->asBoolean;         /* false */
```

**`asJsonValue`** - Converts the set to a JSON-compatible array value. Recursively calls `asJsonValue` on each element
```walnut
Set<T>->asJsonValue(Null => JsonValue)
```

**`asArray`** - Returns the set elements as an array. Alias for `values`
```walnut
Set<T>->asArray(Null => Array<T>)

[1; 2; 3]->asArray;  /* [1, 2, 3] */
```

## 11. Mutable Methods

Methods available on `Mutable<T>` container types. Mutable containers hold a value that can be changed in place. UPPERCASE method names indicate mutation of the contained value.

### 11.1 Value Access

**`value`** - Get the current value held by the mutable container
```walnut
Mutable<T>->value(Null => T)

x = mutable{Integer, 5};
x->value;  /* 5 */
```

**`item`** - Access an item of the contained collection value (delegates to the inner value's `item` method)
```walnut
Mutable<Array<T>>->item(Integer => Result<T, ItemNotFound>)

x = mutable{Array<String>, ['a', 'b', 'c']};
x->item(1);  /* 'b' */
```

### 11.2 Mutation

**`SET`** - Replace the contained value with a new value
```walnut
Mutable<T>->SET(T => Mutable<T>)

x = mutable{Integer, 5};
x->SET(10);  /* Mutable<Integer> containing 10 */
```

### 11.3 Collection Mutations (Array/Tuple)

**`PUSH`** - Append an item to the end of a mutable array (requires unbounded max length)
```walnut
Mutable<Array<T>>->PUSH(T => Mutable<Array<T>>)

x = mutable{Array<Integer>, [1, 2, 3]};
x->PUSH(4);  /* Mutable containing [1, 2, 3, 4] */
```

**`POP`** - Remove and return the last item from a mutable array
```walnut
Mutable<Array<T>>->POP(Null => Result<T, ItemNotFound>)

x = mutable{Array<Integer>, [1, 2, 3]};
x->POP;  /* 3 (x now contains [1, 2]) */
```

**`UNSHIFT`** - Prepend an item to the beginning of a mutable array (requires unbounded max length)
```walnut
Mutable<Array<T>>->UNSHIFT(T => Mutable<Array<T>>)

x = mutable{Array<Integer>, [1, 2, 3]};
x->UNSHIFT(0);  /* Mutable containing [0, 1, 2, 3] */
```

**`SHIFT`** - Remove and return the first item from a mutable array
```walnut
Mutable<Array<T>>->SHIFT(Null => Result<T, ItemNotFound>)

x = mutable{Array<Integer>, [1, 2, 3]};
x->SHIFT;  /* 1 (x now contains [2, 3]) */
```

**`APPEND`** - Append a string to a mutable string (requires unbounded max length)
```walnut
Mutable<String>->APPEND(String => Mutable<String>)

x = mutable{String, 'hello'};
x->APPEND(' world');  /* Mutable containing 'hello world' */
```

**`REVERSE`** - Reverse the contained array or string in place
```walnut
Mutable<Array<T>>->REVERSE(Null => Mutable<Array<T>>)
Mutable<String>->REVERSE(Null => Mutable<String>)

x = mutable{Array<Integer>, [1, 2, 3]};
x->REVERSE;  /* Mutable containing [3, 2, 1] */
```

**`SHUFFLE`** - Randomly shuffle the items of a mutable array in place
```walnut
Mutable<Array<T>>->SHUFFLE(Null => Mutable<Array<T>>)

x = mutable{Array<Integer>, [1, 2, 3]};
x->SHUFFLE;  /* Mutable containing items in random order */
```

**`SORT`** - Sort the contained array, map, or set in place (items must be String or Integer|Real)
```walnut
Mutable<Array<T>>->SORT(Null|[reverse: Boolean] => Mutable<Array<T>>)
Mutable<Map<K:T>>->SORT(Null|[reverse: Boolean] => Mutable<Map<K:T>>)
Mutable<Set<T>>->SORT(Null|[reverse: Boolean] => Mutable<Set<T>>)

x = mutable{Array<Integer>, [3, 1, 2]};
x->SORT;             /* Mutable containing [1, 2, 3] */
x->SORT([reverse: true]);  /* Mutable containing [3, 2, 1] */
```

**`KEYSORT`** - Sort a mutable map by its keys
```walnut
Mutable<Map<K:T>>->KEYSORT(Null|[reverse: Boolean] => Mutable<Map<K:T>>)

x = mutable{Map<Integer>, [b: 2, a: 1, c: 3]};
x->KEYSORT;  /* Mutable containing [a: 1, b: 2, c: 3] */
```

**`FILTER`** - Remove items from a mutable collection that do not satisfy the predicate (requires min length 0)
```walnut
Mutable<Array<T>>->FILTER(^T => Boolean => Mutable<Array<T>>)
Mutable<Map<K:T>>->FILTER(^T => Boolean => Mutable<Map<K:T>>)
Mutable<Set<T>>->FILTER(^T => Boolean => Mutable<Set<T>>)

x = mutable{Array<Integer>, [1, 2, 3, 4]};
x->FILTER(^n: Integer => Boolean :: n > 2);  /* Mutable containing [3, 4] */
```

**`MAP`** - Transform each item in a mutable collection in place (callback return type must match item type)
```walnut
Mutable<Array<T>>->MAP((^T => T) => Mutable<Array<T>>)
Mutable<Map<K:T>>->MAP((^T => T) => Mutable<Map<K:T>>)
Mutable<Set<T>>->MAP((^T => T) => Mutable<Set<T>>)

x = mutable{Array<Integer>, [1, 2, 3]};
x->MAP(^n: Integer => Integer :: n * 2);  /* Mutable containing [2, 4, 6] */
```

**`REMAPKEYS`** - Transform the keys of a mutable map using a callback function
```walnut
Mutable<Map<K:T>>->REMAPKEYS(^K => K => Mutable<Map<T>>), where K is a subtype of String

x = mutable{Map<Integer>, [a: 1, b: 2]};
x->REMAPKEYS(^k: String => String :: k->concatList(['_new']));  /* Mutable containing [a_new: 1, b_new: 2] */
```

### 11.4 Set/Map Mutations

**`ADD`** - Add an item to a mutable set, or add a key-value pair to a mutable map/record
```walnut
Mutable<Set<T>>->ADD(T => Mutable<Set<T>>)
Mutable<Map<K:T>>->ADD([key: K, value: T] => Mutable<Map<K:T>>), where K is a subtype of String
Mutable<Record>->ADD([key: String, value: Any] => Mutable<Record>)

x = mutable{Set<Integer>, {1, 2, 3}};
x->ADD(4);  /* Mutable containing {1, 2, 3, 4} */

y = mutable{Map<Integer>, [a: 1]};
y->ADD([key: 'b', value: 2]);  /* Mutable containing [a: 1, b: 2] */
```

**`REMOVE`** - Remove an item from a mutable set (by value) or map/record (by key)
```walnut
Mutable<Set<T>>->REMOVE(T => Result<T, ItemNotFound>)
Mutable<Map<K:T>>->REMOVE(K => Result<T, MapItemNotFound>), where K is a subtype of String
Mutable<Record>->REMOVE(String => Result<ValueType, MapItemNotFound>)

x = mutable{Set<Integer>, {1, 2, 3}};
x->REMOVE(2);  /* 2 (x now contains {1, 3}) */
```

**`CLEAR`** - Remove all items from a mutable set or map (requires min length 0)
```walnut
Mutable<Set<T>>->CLEAR(Null => Mutable<Set<T>>)
Mutable<Map<K:T>>->CLEAR(Null => Mutable<Map<K:T>>)

x = mutable{Set<Integer>, {1, 2, 3}};
x->CLEAR;  /* Mutable containing {} */
```

### 11.5 Type Casts

These methods delegate to the corresponding method on the contained value. They are available when the contained type supports the respective cast.

**`asInteger`** - Convert the contained value to Integer (delegates to inner value's `asInteger`)
```walnut
Mutable<T>->asInteger(Any => Integer)

x = mutable{Real, 3.14};
x->asInteger;  /* 3 */
```

**`asReal`** - Convert the contained value to Real (delegates to inner value's `asReal`)
```walnut
Mutable<T>->asReal(Null => Real)

x = mutable{Integer, 42};
x->asReal;  /* 42.0 */
```

**`asString`** - Convert the contained value to String (delegates to inner value's `asString`)
```walnut
Mutable<T>->asString(Null => String)

x = mutable{Integer, 42};
x->asString;  /* '42' */
```

**`asBoolean`** - Convert the contained value to Boolean (delegates to inner value's `asBoolean`)
```walnut
Mutable<T>->asBoolean(Null => Boolean)

x = mutable{Integer, 0};
x->asBoolean;  /* false */
```

**`asJsonValue`** - Convert the contained value to a JSON value (delegates to inner value's `asJsonValue`)
```walnut
Mutable<T>->asJsonValue(Null => JsonValue)

x = mutable{Integer, 42};
x->asJsonValue;  /* JsonValue representation */
```

## 12. Function Methods

Methods available on function types (`^ParamType => ReturnType`).

**`invoke`** - Call a function with the given argument
```walnut
(^P => R)->invoke(P => R)

fn = ^x: Integer => Integer :: x + 1;
fn->invoke(5);  /* 6 */
```

**`forceInvoke`** - Call a function, returning a Result if the parameter type does not match at runtime
```walnut
(^P => R)->forceInvoke(Any => R|Result<R, InvocationError>)

fn = ^x: Integer<1..100> => Integer :: x * 2;
fn->forceInvoke(50);   /* 100 */
fn->forceInvoke(200);  /* Result error: InvocationError */
```

**`+` (compose)** - Compose two functions: `(f + g)(x)` equals `g(f(x))`
```walnut
(^A => B)->binaryPlus((^B => C) => (^A => C))

f = ^x: Integer => Integer :: x + 1;
g = ^x: Integer => String :: x->asString;
h = f + g;
h->invoke(5);  /* '6' */
```

**`*` (compose with error unwrap)** - Compose two functions, automatically unwrapping ExternalError from the first function's result before passing to the second
```walnut
(^A => Result<B, ExternalError>)->binaryMultiply((^B => C) => (^A => Result<C, ExternalError>))

/* Composes f and g where f may return ExternalError;
   the ExternalError is propagated, other results are passed to g */
```

**`&` (compose with error propagation)** - Compose two functions, unwrapping any error from the first function's result before passing the success value to the second; errors are propagated
```walnut
(^A => Result<B, E>)->binaryBitwiseAnd((^B => C) => (^A => Result<C, E>))

/* If f returns an error, the error is propagated;
   otherwise the success value is passed to g */
```

**`|` (compose with error fallback)** - Compose two functions as a fallback chain: if the first function returns an error, the second function is called with the original argument
```walnut
(^A => Result<B, E>)->binaryBitwiseOr((^A => C) => (^A => B|C))

/* If f(x) succeeds, return the result;
   if f(x) returns an error, return g(x) instead */
```

## 13. Result Methods

Methods available on `Result<T, E>` types. These proxy methods delegate to the underlying success value's method. If the Result is an error, the error is propagated unchanged.

**`map`** - Apply a map callback to the success value of a Result, propagating errors
```walnut
Result<Array<T>, E>->map(^T => R => Result<Array<R>, E>)

result = getItems();  /* Result<Array<Integer>, SomeError> */
result->map(^n: Integer => String :: n->asString);
/* If success: maps items; if error: returns the error unchanged */
```

**`mapIndexValue`** - Apply a map-with-index callback to the success value of a Result, propagating errors
```walnut
Result<Array<T>, E>->mapIndexValue(^[Integer, T] => R => Result<Array<R>, E>)

result = getItems();
result->mapIndexValue(^[idx: Integer, val: String] => String :: idx->asString);
```

**`mapKeyValue`** - Apply a map-with-key callback to the success value of a Result, propagating errors
```walnut
Result<Map<T>, E>->mapKeyValue(^[String, T] => R => Result<Map<R>, E>)

result = getMap();
result->mapKeyValue(^[key: String, val: Integer] => String :: key);
```

**`filter`** - Apply a filter callback to the success value of a Result, propagating errors
```walnut
Result<Array<T>, E>->filter(^T => Boolean => Result<Array<T>, E>)

result = getItems();
result->filter(^n: Integer => Boolean :: n > 0);
```

**`reduce`** - Apply a reduce callback to the success value of a Result, propagating errors
```walnut
Result<Array<T>, E>->reduce(^[Acc, T] => Acc => Result<Acc, E>)

result = getItems();
result->reduce(^[acc: Integer, n: Integer] => Integer :: acc + n);
```

**`error`** - Extract the error value from a Result (only available on the error variant)
```walnut
Result<Nothing, E>->error(Null => E)

err = @SomeError;
err->error;  /* SomeError value */
```

## 14. Open Type Methods

Methods available on open types (e.g. `UserId := #Integer<1..>`).

**`value`** - Extract the underlying value from an open type instance
```walnut
OpenType->value(Null => UnderlyingType)

UserId = #Integer<1..>;
id = UserId(42);
id->value;  /* 42 */
```

**`with`** - Create a new instance of the open type with a modified underlying value (for record/array-based open types)
```walnut
OpenType->with(PartialValueType => OpenType|Result<OpenType, ValidationError>)

Email = #String<1..100> @ {:: ... };
UserId = #[name: String, age: Integer];
user = UserId([name: 'Alice', age: 30]);
user->with([age: 31]);  /* UserId([name: 'Alice', age: 31]) */

/* If the open type has a validator, the result may be a Result type */
```

**`item`** - Access an item within the underlying value of an open type (delegates to inner value's `item`)
```walnut
OpenType->item(KeyType => Result<ValueType, Error>)

Ids = #Array<Integer>;
ids = Ids([1, 2, 3]);
ids->item(0);  /* 1 */
```

**`asJsonValue`** - Convert the open type's underlying value to a JSON value (delegates to inner value's `asJsonValue`)
```walnut
OpenType->asJsonValue(Null => JsonValue)

UserId = #Integer<1..>;
id = UserId(42);
id->asJsonValue;  /* JsonValue representation */
```

## 15. Data Type Methods

Methods available on data types (e.g. `Product = $[id: Integer, name: String]`).

**`value`** - Extract the underlying value from a data type instance
```walnut
DataType->value(Null => UnderlyingType)

Product = $[id: Integer, name: String];
p = Product([id: 1, name: 'Widget']);
p->value;  /* [id: 1, name: 'Widget'] */
```

**`with`** - Create a new instance of the data type with partially updated fields
```walnut
DataType->with(PartialValueType => DataType)

Product = $[id: Integer, name: String];
p = Product([id: 1, name: 'Widget']);
p->with([name: 'Gadget']);  /* Product([id: 1, name: 'Gadget']) */
```

**`item`** - Access a field of the underlying value (delegates to inner value's `item`)
```walnut
DataType->item(KeyType => Result<ValueType, Error>)

Product = $[id: Integer, name: String];
p = Product([id: 1, name: 'Widget']);
p->item('name');  /* 'Widget' */
```

**`asJsonValue`** - Convert the data type's underlying value to a JSON value (delegates to inner value's `asJsonValue`)
```walnut
DataType->asJsonValue(Null => JsonValue)

Product = $[id: Integer, name: String];
p = Product([id: 1, name: 'Widget']);
p->asJsonValue;  /* JsonValue representation */
```

## 16. Sealed Type Methods

Methods available on sealed types (e.g. `$$SealedSubtype`).

**`asJsonValue`** - Convert the sealed type's underlying value to a JSON value (delegates to inner value's `asJsonValue`)
```walnut
SealedType->asJsonValue(Null => JsonValue)

mySealed = $$SomeSubtype([field: 'value']);
mySealed->asJsonValue;  /* JsonValue representation */
```

## 17. Record Methods

**`with`** - Create a new record by merging the target record with the parameter record (parameter values override target values for matching keys)
```walnut
[key1: T1, key2: T2]->with([key1: T3, ...] => [key1: T1|T3, key2: T2, ...])

r = [name: 'Alice', age: 30];
r->with([age: 31]);  /* [name: 'Alice', age: 31] */

r->with([city: 'NYC']);  /* [name: 'Alice', age: 30, city: 'NYC'] */
```

## 18. Tuple Methods

No dedicated Tuple-specific native methods were found in the codebase. Tuple values use the general collection methods available on arrays and sequences.

## 19. Type Methods

Methods available on type values (backtick-prefixed types like `` `Integer ``, `` `String ``). Type values are of type `Type<T>` and these methods enable runtime type introspection and construction.

### 19.1 Type Introspection

**`typeName`** - Returns the name of a named type as a string
```walnut
Type<Named|Atom|Enumeration|Alias|Data|Open|Sealed>->typeName(Null => String)

`MyType->typeName;  /* 'MyType' */
```

**`minValue`** - Returns the minimum value of a numeric type, or MinusInfinity
```walnut
Type<Integer|Real>->minValue(Null => Integer|Real|MinusInfinity)

`Integer<1..100>->minValue;  /* 1 */
`Integer->minValue;          /* MinusInfinity */
```

**`maxValue`** - Returns the maximum value of a numeric type, or PlusInfinity
```walnut
Type<Integer|Real>->maxValue(Null => Integer|Real|PlusInfinity)

`Integer<1..100>->maxValue;  /* 100 */
`Integer->maxValue;          /* PlusInfinity */
```

**`minLength`** - Returns the minimum length constraint of a length-bounded type
```walnut
Type<String|Array|Map|Set>->minLength(Null => Integer<0..>)

`String<3..10>->minLength;    /* 3 */
`Array<Integer>->minLength;   /* 0 */
```

**`maxLength`** - Returns the maximum length constraint of a length-bounded type, or PlusInfinity
```walnut
Type<String|Array|Map|Set>->maxLength(Null => Integer<0..>|PlusInfinity)

`String<3..10>->maxLength;  /* 10 */
`String->maxLength;         /* PlusInfinity */
```

**`itemType`** - Returns the item type of a collection type
```walnut
Type<Array|Map|Set>->itemType(Null => Type)

`Array<Integer>->itemType;       /* `Integer */
`Map<String, Boolean>->itemType; /* `Boolean */
```

**`itemTypes`** - Returns the component types of a Tuple, Record, Union, or Intersection type
```walnut
Type<Tuple|Record|Union|Intersection>->itemTypes(Null => Array<Type>|Record<Type>)

/* For Tuple: returns Array of the element types */
`[Integer, String]->itemTypes;  /* [`Integer, `String] */

/* For Record: returns Record mapping field names to their types */
`[name: String, age: Integer]->itemTypes;  /* [name: `String, age: `Integer] */

/* For Union/Intersection at runtime: returns Array of component types */
```

**`keyType`** - Returns the key type of a Map type
```walnut
Type<Map>->keyType(Null => Type)

`Map<String, Integer>->keyType;  /* `String */
```

**`parameterType`** - Returns the parameter type of a Function type
```walnut
Type<Function>->parameterType(Null => Type)

`^Integer => String->parameterType;  /* `Integer */
```

**`returnType`** - Returns the return type of a Function or Result type
```walnut
Type<Function|Result>->returnType(Null => Type)

`^Integer => String->returnType;         /* `String */
`Result<String, Error>->returnType;      /* `String */
```

**`errorType`** - Returns the error type of a Result type
```walnut
Type<Result>->errorType(Null => Type)

`Result<String, Error>->errorType;  /* `Error */
```

**`refType`** - Returns the referenced type of a Type or Shape type
```walnut
Type<Type|Shape>->refType(Null => Type)

/* Returns the type that a Type<T> or Shape<T> wraps */
```

**`restType`** - Returns the rest type of a Tuple or Record type
```walnut
Type<Tuple|Record>->restType(Null => Type)

/* For Tuple/Record types that have a rest element */
`[Integer, String, ... Real]->restType;  /* `Real */
```

**`valueType`** - Returns the value type of Open, Sealed, Data, Mutable, or OptionalKey types
```walnut
Type<Open|Sealed|Data|Mutable|OptionalKey>->valueType(Null => Type)

`Mutable<Integer>->valueType;  /* `Integer */
```

**`values`** - Returns the values of a subset type as an Array
```walnut
Type<IntegerSubset>->values(Null => Array<Integer>)
Type<RealSubset>->values(Null => Array<Real>)
Type<StringSubset>->values(Null => Array<String>)
Type<EnumerationSubset>->values(Null => Array<EnumerationSubset>)

`Integer<1|2|3>->values;        /* [1, 2, 3] */
`String<'a'|'b'>->values;       /* ['a', 'b'] */
```

**`valuesSet`** - Returns the values of a subset type as a Set
```walnut
Type<IntegerSubset>->valuesSet(Null => Set<Integer>)
Type<RealSubset>->valuesSet(Null => Set<Real>)
Type<StringSubset>->valuesSet(Null => Set<String>)
Type<EnumerationSubset>->valuesSet(Null => Set<EnumerationSubset>)

`Integer<1|2|3>->valuesSet;  /* {1, 2, 3} */
```

**`numberRange`** - Returns the number range of an Integer or Real type as a structured record
```walnut
Type<Integer|Real>->numberRange(Null => IntegerNumberRange|RealNumberRange)

/* Returns a structured representation with intervals, each having start/end endpoints */
`Integer<1..100>->numberRange;
/* IntegerNumberRange([intervals: [IntegerNumberInterval([start: ..., end: ...])]]) */
```

**`isSubtypeOf`** - Tests whether the target type is a subtype of the parameter type
```walnut
Type->isSubtypeOf(Type => Boolean)

`Integer->isSubtypeOf(`Real);              /* true */
`Integer<1..10>->isSubtypeOf(`Integer);    /* true */
`String->isSubtypeOf(`Integer);            /* false */
```

**`aliasedType`** - Returns the underlying type of an alias type
```walnut
Type<Alias>->aliasedType(Null => Type)

/* Given: Age = Integer<0..150>; */
`Age->aliasedType;  /* `Integer<0..150> */
```

**`atomValue`** - Returns the singleton value of an Atom type
```walnut
Type<Atom>->atomValue(Null => Atom)

/* Given: MyAtom = :[];  */
`MyAtom->atomValue;  /* MyAtom */
```

**`enumerationType`** - Returns the parent enumeration type of an enumeration subset type
```walnut
Type<EnumerationSubset>->enumerationType(Null => Type<Enumeration>)

/* Given: Color = :[Red, Green, Blue]; */
/* Given a subset like Color[Red, Green] */
`Color[Red, Green]->enumerationType;  /* `Color */
```

**`valueWithName`** - Looks up an enumeration value by its name string, returning a Result
```walnut
Type<EnumerationSubset|Enumeration>->valueWithName(String => Result<Enumeration, UnknownEnumerationValue>)

/* Given: Color = :[Red, Green, Blue]; */
`Color->valueWithName('Red');      /* Color.Red */
`Color->valueWithName('Unknown');  /* Error(UnknownEnumerationValue) */
```

**`openApiSchema`** - Generates an OpenAPI-compatible JSON schema for the type
```walnut
Type->openApiSchema(Null => JsonValue)

/* Target type must be a subtype of JsonValue */
`Integer<1..100>->openApiSchema;
/* [type: 'integer', minimum: 1, maximum: 100] */

`String<1..50>->openApiSchema;
/* [type: 'string', minLength: 1, maxLength: 50] */

`Array<Integer>->openApiSchema;
/* [type: 'array', items: [type: 'integer']] */

`[name: String, age: Integer]->openApiSchema;
/* [type: 'object', properties: [name: [type: 'string'], age: [type: 'integer']], required: ['name', 'age']] */
```

### 19.2 Type Construction

**`withNumberRange`** - Returns a new numeric type with the specified number range
```walnut
Type<Integer>->withNumberRange(IntegerNumberRange => Type<Integer>)
Type<Real>->withNumberRange(RealNumberRange => Type<Real>)

/* Constructs a numeric type with full interval-based range specification */
```

**`withLengthRange`** - Returns a new length-bounded type with the specified length range
```walnut
Type<String|Array|Map|Set>->withLengthRange(LengthRange => Type<String|Array|Map|Set>)

/* Given: LengthRange = [minLength: Integer<0..>, maxLength: Integer<0..>|PlusInfinity] */
`String->withLengthRange([minLength: 1, maxLength: 100]);  /* `String<1..100> */
`Array<Integer>->withLengthRange([minLength: 0, maxLength: 10]);
```

**`withItemType`** - Returns a new collection type with the specified item type
```walnut
Type<Array|Map|Set>->withItemType(Type => Type<Array|Map|Set>)

`Array<Any>->withItemType(`Integer);   /* `Array<Integer> */
`Set<Any>->withItemType(`String);      /* `Set<String> */
```

**`withItemTypes`** - Returns a new Tuple, Record, Union, or Intersection type with the specified component types
```walnut
Type<Tuple>->withItemTypes(Array<Type> => Type<Tuple>)
Type<Record>->withItemTypes(Map<String:Type> => Type<Record>)
Type<Union>->withItemTypes(Array<Type> => Type<Union>)
Type<Intersection>->withItemTypes(Array<Type> => Type<Intersection>)

/* Tuple example: */
`[Any]->withItemTypes([`Integer, `String]);  /* `[Integer, String] */

/* Record example: */
`[name: Any]->withItemTypes([name: `String, age: `Integer]);
```

**`withKeyType`** - Returns a new Map type with the specified key type (must be a String subtype)
```walnut
Type<Map>->withKeyType(Type<String> => Type<Map>)

`Map<String, Integer>->withKeyType(`String<'a'|'b'>);
```

**`withParameterType`** - Returns a new Function type with the specified parameter type
```walnut
Type<Function>->withParameterType(Type => Type<Function>)

`^Any => String->withParameterType(`Integer);  /* `^Integer => String */
```

**`withReturnType`** - Returns a new Function or Result type with the specified return type
```walnut
Type<Function>->withReturnType(Type => Type<Function>)
Type<Result>->withReturnType(Type => Type<Result>)

`^Integer => Any->withReturnType(`String);         /* `^Integer => String */
`Result<Any, Error>->withReturnType(`String);       /* `Result<String, Error> */
```

**`withErrorType`** - Returns a new Result type with the specified error type
```walnut
Type<Result>->withErrorType(Type => Type<Result>)

`Result<String, Any>->withErrorType(`MyError);  /* `Result<String, MyError> */
```

**`withRefType`** - Returns a new Type or Shape type with the specified referenced type
```walnut
Type<Type|Shape>->withRefType(Type => Type<Type|Shape>)

/* Constructs a Type<T> or Shape<T> wrapping the given type */
```

**`withRestType`** - Returns a new Tuple or Record type with the specified rest type
```walnut
Type<Tuple|Record>->withRestType(Type => Type<Tuple|Record>)

`[Integer, String]->withRestType(`Real);  /* `[Integer, String, ... Real] */
```

**`withValueType`** - Returns a new Mutable or OptionalKey type with the specified value type
```walnut
Type<Mutable|OptionalKey>->withValueType(Type => Type<Mutable|OptionalKey>)

`Mutable<Any>->withValueType(`Integer);  /* `Mutable<Integer> */
```

**`withValues`** `[REVIEW]` - Returns a new subset type constrained to the specified values
```walnut
Type<Integer>->withValues(Set<Integer> => Type<IntegerSubset>)
Type<Real>->withValues(Set<Real> => Type<RealSubset>)
Type<String>->withValues(Set<String> => Type<StringSubset>)
Type<Enumeration>->withValues(Set<Enumeration> => Result<Type<EnumerationSubset>, UnknownEnumerationValue>)

`Integer->withValues([1, 2, 3]);     /* `Integer<1|2|3> */
`String->withValues(['a', 'b']);     /* `String<'a'|'b'> */
```

**`withRange`** - Returns a new numeric type with the specified min/max range (simplified form)
```walnut
Type<Integer>->withRange(IntegerRange => Type<Integer>)
Type<Real>->withRange(RealRange => Type<Real>)

/* IntegerRange = [minValue: Integer|MinusInfinity, maxValue: Integer|PlusInfinity] */
`Integer->withRange([minValue: 1, maxValue: 100]);  /* `Integer<1..100> */
`Real->withRange([minValue: 0.0, maxValue: 1.0]);   /* `Real<0.0..1.0> */
```

### 19.3 Type Operators

**`binaryBitwiseAnd`** (`&`) - Computes the intersection of two types
```walnut
Type->binaryBitwiseAnd(Type => Type)

`Integer & `Real;  /* intersection type */
`A & `B;           /* Type that satisfies both A and B */
```

**`binaryBitwiseOr`** (`|`) - Computes the union of two types
```walnut
Type->binaryBitwiseOr(Type => Type)

`Integer | `String;  /* Integer|String union type */
`Null | `Integer;    /* Null|Integer (optional integer) */
```

**`binaryLessThan`** (`<`) - Tests whether the target type is a strict subtype of the parameter type
```walnut
Type->binaryLessThan(Type => Boolean)

`Integer<1..10> < `Integer;  /* true (strict subtype) */
`Integer < `Integer;         /* false (not strict) */
```

**`binaryLessThanEqual`** (`<=`) - Tests whether the target type is a subtype of (or equal to) the parameter type
```walnut
Type->binaryLessThanEqual(Type => Boolean)

`Integer<1..10> <= `Integer;  /* true */
`Integer <= `Integer;         /* true */
```

**`binaryGreaterThan`** (`>`) - Tests whether the target type is a strict supertype of the parameter type
```walnut
Type->binaryGreaterThan(Type => Boolean)

`Integer > `Integer<1..10>;  /* true (strict supertype) */
`Integer > `Integer;         /* false (not strict) */
```

**`binaryGreaterThanEqual`** (`>=`) - Tests whether the target type is a supertype of (or equal to) the parameter type
```walnut
Type->binaryGreaterThanEqual(Type => Boolean)

`Integer >= `Integer<1..10>;  /* true */
`Integer >= `Integer;         /* true */
```

## 20. Bytes Methods

Bytes represents raw binary data. Bytes values are written with double quotes (e.g., `"hello"`) and operate at the byte level rather than the character level. The `Bytes` type supports an optional length range constraint: `Bytes<min..max>`.

### 20.1 Properties

**`length`** - Returns the number of bytes
```walnut
Bytes->length(Null => Integer)

"hello"->length;  /* 5 */
""->length;       /* 0 */
```

**`ord`** - Returns the byte value (0-255) of a single-byte Bytes value
```walnut
Bytes<1>->ord(Null => Integer<0..255>)

"A"->ord;  /* 65 */
"!"->ord;  /* 33 */
```

### 20.2 Search and Test

**`contains`** - Tests if the target contains the given bytes
```walnut
Bytes->contains(Bytes => Boolean)

"hello world"->contains("world");  /* true */
"hello"->contains("xyz");          /* false */
```

**`startsWith`** - Tests if the target starts with the given bytes
```walnut
Bytes->startsWith(Bytes => Boolean)

"hello world"->startsWith("hello");  /* true */
"hello"->startsWith("xyz");          /* false */
```

**`endsWith`** - Tests if the target ends with the given bytes
```walnut
Bytes->endsWith(Bytes => Boolean)

"hello world"->endsWith("world");  /* true */
"hello"->endsWith("xyz");          /* false */
```

**`positionOf`** - Returns the position of the first occurrence of a byte sequence, or an error if not found
```walnut
Bytes->positionOf(Bytes => Result<Integer<0..>, SliceNotInBytes>)

"hello"->positionOf("ll");  /* 2 */
"hello"->positionOf("xyz"); /* @SliceNotInBytes */
```

**`lastPositionOf`** - Returns the position of the last occurrence of a byte sequence, or an error if not found
```walnut
Bytes->lastPositionOf(Bytes => Result<Integer<0..>, SliceNotInBytes>)

"hello hello"->lastPositionOf("hello");  /* 6 */
"hello"->lastPositionOf("xyz");          /* @SliceNotInBytes */
```

### 20.3 Operators

**`binaryPlus`** (`+`) - Concatenates two Bytes values, or appends a byte by integer value (0-255)
```walnut
Bytes->binaryPlus(Bytes => Bytes)
Bytes->binaryPlus(Integer<0..255> => Bytes)

"hello " + "world";  /* "hello world" */
"hello" + 33;         /* "hello!" */
```

**`binaryMinus`** (`-`) - Removes all occurrences of specified byte(s) from the target
```walnut
Bytes->binaryMinus(Bytes<1>|Array<Bytes<1>>|Set<Bytes<1>> => Bytes)

"hello" - "l";          /* "heo" */
"hello" - ["l", "o"];   /* "he" */
```

**`binaryMultiply`** (`*`) - Repeats the bytes a given number of times
```walnut
Bytes->binaryMultiply(Integer<0..> => Bytes)

"ab" * 3;  /* "ababab" */
"x" * 0;   /* "" */
```

**`binaryGreaterThan`** (`>`) - Lexicographic comparison
```walnut
Bytes->binaryGreaterThan(Bytes => Boolean)

"b" > "a";  /* true */
```

**`binaryGreaterThanEqual`** (`>=`) - Lexicographic comparison
```walnut
Bytes->binaryGreaterThanEqual(Bytes => Boolean)

"a" >= "a";  /* true */
```

**`binaryLessThan`** (`<`) - Lexicographic comparison
```walnut
Bytes->binaryLessThan(Bytes => Boolean)

"a" < "b";  /* true */
```

**`binaryLessThanEqual`** (`<=`) - Lexicographic comparison
```walnut
Bytes->binaryLessThanEqual(Bytes => Boolean)

"a" <= "a";  /* true */
```

**`binaryBitwiseAnd`** (`&`) - Performs byte-by-byte bitwise AND, padding the shorter operand with zero bytes on the left
```walnut
Bytes->binaryBitwiseAnd(Bytes => Bytes)
```

**`binaryBitwiseOr`** (`|`) - Performs byte-by-byte bitwise OR, padding the shorter operand with zero bytes on the left
```walnut
Bytes->binaryBitwiseOr(Bytes => Bytes)
```

**`binaryBitwiseXor`** (`^`) - Performs byte-by-byte bitwise XOR, padding the shorter operand with zero bytes on the left
```walnut
Bytes->binaryBitwiseXor(Bytes => Bytes)
```

**`unaryBitwiseNot`** (`~`) - Performs byte-by-byte bitwise NOT (complement)
```walnut
Bytes->unaryBitwiseNot(Null => Bytes)

~~~"A";  /* each byte is bitwise-inverted */
```

**`reverse`** / **`unaryMinus`** (`-`) - Reverses the byte order
```walnut
Bytes->reverse(Null => Bytes)
Bytes->unaryMinus(Null => Bytes)

"abc"->reverse;  /* "cba" */
-"abc";           /* "cba" */
```

### 20.4 Slicing and Splitting

**`slice`** - Extracts a sub-sequence of bytes by start position and length
```walnut
Bytes->slice([start: Integer<0..>, length: Integer<0..>] => Bytes)

"hello"->slice[start: 1, length: 2];  /* "el" */
```

**`sliceRange`** - Extracts a sub-sequence of bytes by start and end positions
```walnut
Bytes->sliceRange([start: Integer<0..>, end: Integer<0..>] => Bytes)

"hello"->sliceRange[start: 1, end: 4];  /* "ell" */
```

**`chunk`** - Splits bytes into fixed-size chunks
```walnut
Bytes->chunk(Integer<1..> => Array<Bytes>)

"abcdef"->chunk(2);  /* ["ab", "cd", "ef"] */
```

**`split`** - Splits bytes by a delimiter
```walnut
Bytes->split(Bytes<1..> => Array<Bytes>)

"a-b-c"->split("-");  /* ["a", "b", "c"] */
```

### 20.5 Concatenation and Modification

**`concat`** - Concatenates two Bytes values (same as `+` for Bytes)
```walnut
Bytes->concat(Bytes => Bytes)

"hello"->concat(" world");  /* "hello world" */
```

**`concatList`** - Concatenates a list of Bytes values onto the target
```walnut
Bytes->concatList(Array<Bytes> => Bytes)

""->concatList["hello", " ", "world"];  /* "hello world" */
```

**`replace`** - Replaces occurrences of a byte pattern (or regular expression) with a replacement
```walnut
Bytes->replace([match: Bytes|RegExp, replacement: Bytes] => Bytes)

"hello"->replace[match: "l", replacement: "r"];  /* "herro" */
```

**`padLeft`** - Pads the bytes on the left to reach a specified length
```walnut
Bytes->padLeft([length: Integer, padBytes: Bytes] => Bytes)

"42"->padLeft[length: 5, padBytes: "0"];  /* "00042" */
```

**`padRight`** - Pads the bytes on the right to reach a specified length
```walnut
Bytes->padRight([length: Integer, padBytes: Bytes] => Bytes)

"42"->padRight[length: 5, padBytes: "."];  /* "42..." */
```

### 20.6 Conversion

**`asBoolean`** - Returns `true` for non-empty bytes, `false` for empty bytes
```walnut
Bytes->asBoolean(Null => Boolean)

"hello"->asBoolean;  /* true */
""->asBoolean;        /* false */
```

**`asString`** - Converts bytes to a UTF-8 string, returning an error if the bytes are not valid UTF-8
```walnut
Bytes->asString(Null => Result<String, InvalidString>)

"hello"->asString;  /* 'hello' */
```

## 21. JsonValue Methods

`JsonValue` is a union type representing all JSON-compatible values: `Null|Boolean|String|Real|Array<JsonValue>|Map<String:JsonValue>|Set<JsonValue>|Mutable<JsonValue>`.

**`stringify`** - Converts a JSON-compatible value to a JSON string representation
```walnut
JsonValue->stringify(Null => String<2..>)

[1, 2, 3]->stringify;                  /* '[1, 2, 3]' (pretty-printed) */
[name: 'Alice', age: 30]->stringify;   /* JSON object string */
null->stringify;                        /* 'null' */
```

**`hydrateAs`** - Attempts to convert a value into a specified target type via hydration. Returns the hydrated value or a HydrationError.
```walnut
JsonValue->hydrateAs(Type<T> => Result<T, HydrationError>)

data = [name: 'Alice', age: 30];
data->hydrateAs(type[name: String, age: Integer]);  /* [name: 'Alice', age: 30] typed as the record */
```

**`asJsonValue`** - Casts a supported value to the `JsonValue` type
```walnut
Any->asJsonValue(Null => JsonValue)

42->asJsonValue;  /* 42 as JsonValue */
```

## 22. RegExp Methods

`RegExp` is a sealed subtype of `String` that validates its contents as a valid regular expression pattern. It is defined as `RegExp := $String @ InvalidRegExp`.

**`matchString`** - Matches a regular expression against a string, returning match details or an error
```walnut
RegExp->matchString(String => Result<RegExpMatch, NoRegExpMatch>)

/* RegExpMatch = [match: String, groups: Array<String>] */
pattern = RegExp('/(\w+)\s(\w+)/');
pattern->matchString('hello world');
/* RegExpMatch[match: 'hello world', groups: ['hello', 'world']] */

pattern->matchString('!!!');
/* @NoRegExpMatch */
```

## 23. Clock Methods

`Clock` is an atom type (`Clock := ()`) representing the system clock. It is typically injected as a dependency.

**`now`** - Returns the current date and time
```walnut
Clock->now(Null => DateAndTime)

/* DateAndTime := #[date: Date, time: Time]          */
/* Date := #[year: Integer, month: Integer<1..12>,    */
/*           day: Integer<1..31>]                     */
/* Time := #[hour: Integer<0..23>,                    */
/*           minute: Integer<0..59>,                  */
/*           second: Integer<0..59>]                  */

clock->now;
/* DateAndTime[date: Date[2026, 2, 17], time: Time[14, 30, 0]] */
```

## 24. Random Methods

`Random` is an atom type (`Random := ()`) used as a source of randomness. It is typically injected as a dependency.

**`integer`** - Generates a random integer within an inclusive range
```walnut
Random->integer([min: Integer, max: Integer] => Integer)

random->integer[min: 1, max: 100];  /* e.g. 42 */
random->integer[min: 0, max: 1];    /* 0 or 1 */
```

**`uuid`** - Generates a random UUID (v4)
```walnut
Random->uuid(Null => Uuid)

/* Uuid := #String<36> */
random->uuid;  /* e.g. Uuid['550e8400-e29b-41d4-a716-446655440000'] */
```

## 25. PasswordString Methods

`PasswordString` is a sealed type (`PasswordString := #[value: String]`) for securely handling passwords.

**`hash`** - Generates a secure password hash using the default algorithm (bcrypt)
```walnut
PasswordString->hash(Null => String<24..255>)

pw = PasswordString[value: 'secret123'];
pw->hash;  /* '$2y$10$...' (bcrypt hash string) */
```

**`verify`** - Verifies a plain password against a previously generated hash
```walnut
PasswordString->verify(String => Boolean)

pw = PasswordString[value: 'secret123'];
hashed = pw->hash;
pw->verify(hashed);  /* true */

wrong = PasswordString[value: 'wrong'];
wrong->verify(hashed);  /* false */
```

## 26. File Methods

`File` is a sealed type (`File := $[path: String]`) representing a file system reference. File operations are effectful and return `Result` types for error handling.

**`content`** - Reads the entire content of a file
```walnut
File->content(Null => Result<String, CannotReadFile>)

/* CannotReadFile := $[file: File] */
file = File[path: '/tmp/data.txt'];
file->content;  /* 'file contents...' or @CannotReadFile */
```

**`appendContent`** - Appends a string to the end of the file
```walnut
File->appendContent(String => Result<String, CannotWriteFile>)

/* CannotWriteFile := $[file: File] */
file = File[path: '/tmp/log.txt'];
file->appendContent('new line\n');  /* the appended string, or @CannotWriteFile */
```

**`replaceContent`** - Replaces the entire content of a file
```walnut
File->replaceContent(String => Result<String, CannotWriteFile>)

file = File[path: '/tmp/data.txt'];
file->replaceContent('new content');  /* the new content, or @CannotWriteFile */
```

**`createIfMissing`** - Creates the file with initial content if it does not already exist; returns the File if it already exists
```walnut
File->createIfMissing(String => Result<File, CannotWriteFile>)

file = File[path: '/tmp/config.txt'];
file->createIfMissing('default config');  /* File or @CannotWriteFile */
```

## 27. RoutePattern Methods

`RoutePattern` is a sealed subtype of `String` (`RoutePattern := #String`) used for URL route matching. Patterns support named placeholders `{name}` for string captures and `{+name}` for integer captures.

**`matchAgainst`** - Matches a URL path against the route pattern, returning extracted parameters or a mismatch atom
```walnut
RoutePattern->matchAgainst(String => Map<String|Integer<0..>>|RoutePatternDoesNotMatch)

/* RoutePatternDoesNotMatch := () */
pattern = RoutePattern('/users/{name}');
pattern->matchAgainst('/users/alice');
/* [name: 'alice'] */

pattern = RoutePattern('/items/{+id}');
pattern->matchAgainst('/items/42');
/* [id: 42] */

pattern = RoutePattern('/about');
pattern->matchAgainst('/about');
/* [] (empty map) */

pattern->matchAgainst('/other');
/* @RoutePatternDoesNotMatch */
```

## 28. DependencyContainer Methods

`DependencyContainer` is an atom type (`DependencyContainer := ()`) that provides dependency injection capabilities. It is typically injected as a dependency itself via the `~~` operator.

**`valueOf`** - Retrieves a value for the requested type from the dependency container
```walnut
DependencyContainer->valueOf(Type<T> => Result<T, DependencyContainerError>)

/* DependencyContainerError := [targetType: Type, errorOnType: Type, errorMessage: String] */
dependencyContainer->valueOf(type{MyService});
/* MyService instance, or @DependencyContainerError */
```

Possible error messages include:
- `'Circular dependency'` -- the dependency graph has a cycle
- `'Ambiguous dependency'` -- multiple candidates match the requested type
- `'Dependency not found'` -- no provider registered for the type
- `'Unsupported type'` -- the type cannot be resolved
- `'Error returned while creating value'` -- a constructor or factory returned an error

## 29. EventBus Methods

`EventBus` is a sealed type (`EventBus := $[listeners: Array<EventListener>]`) that implements a simple publish/subscribe event system. `EventListener` is defined as `EventListener = ^Nothing => *Null` (a function from any type to a possibly-erroring Null).

**`fire`** - Dispatches an event to all compatible listeners. Each listener whose parameter type matches the event value is invoked. If a listener returns an error, propagation stops and the error is returned.
```walnut
EventBus->fire(Any => Result<Any, ExternalError>)

/* ExternalError := $[errorType: String, originalError: Any, errorMessage: String] */
bus = EventBus[listeners: [myHandler]];
bus->fire('some event');
/* 'some event' (returned after all listeners are called), or @ExternalError */
```

---

## Review List

The following methods have complex type-refinement logic that should be manually verified:

### Any
- **`castAs`** - Cast a value to a different type, performing conversion if possible
- **`shape`** - Convert a value to match a structural type shape
- **`construct`** - Construct an instance of an open, sealed, or enumeration type from a value
- **`asMutableOfType`** - Wrap a value in a mutable container of the given type
- **`ifError`** - Handle an error by providing a callback that transforms the error value
- **`when`** - Handle both success and error cases with separate callbacks
- **`apply`** - Apply a function to the target value (pipe the value into the function)

### Integer
- **`binaryMultiply`** (`*`) - Multiply an integer by an integer or real
- **`binaryIntegerDivide`** (`//`) - Integer division of two integers
- **`binaryModulo`** (`%`) - Remainder after division by an integer or real
- **`square`** - Square of an integer
- **`clamp`** - Clamp an integer to a range defined by min and/or max
- **`downTo`** - Generate a descending array of integers from target down to the parameter (inclusive)

### Real
- **`clamp`** - Clamp a real number to a range defined by min and/or max

### Array
- **`take`** - Takes the first N elements
- **`chunk`** - Splits the array into chunks of a given size
- **`binaryModulo`** (`%`) - Returns the remainder elements after chunking by N

### Type
- **`values`** - Returns the values of a subset type as an Array
- **`valuesSet`** - Returns the values of a subset type as a Set
- **`numberRange`** - Returns the number range of an Integer or Real type as a structured record
- **`openApiSchema`** - Generates an OpenAPI-compatible JSON schema for the type
- **`withNumberRange`** - Returns a new numeric type with the specified number range
- **`withLengthRange`** - Returns a new length-bounded type with the specified length range
- **`withItemTypes`** - Returns a new Tuple, Record, Union, or Intersection type with the specified component types
- **`withValues`** - Returns a new subset type constrained to the specified values
- **`withRange`** - Returns a new numeric type with the specified min/max range (simplified form)
