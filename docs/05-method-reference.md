# Method Reference

All the built-in methods in Walnut act on types, and therefore they all define a target type, a parameter type and return type.
The best effort is put to offer the narrowest possible return type based on the target type and the parameter type.
The dependency type is not used in the built-in methods.

Example:
```walnut
sumOfIntegers = ^[a: Integer<5..20>, b: Integer<8..16>] => Integer<13..36> :: #a + #b; /* which is #a->binaryPlus(#b) */
```
In the example above, the built-in method Integer->binaryPlus takes the ranges of the target and the parameter and 
returns an Integer with the narrowest possible range.

Many of the built-in methods are obvious based on their names. The following is a list of the most important ones grouped by the target type.

## Base types

### Any (of every value)
- binaryEqual, binaryNotEqual (==, !=)
- isOfType, type, as, shape, jsonStringify, printed
- _(output to stdout)_ DUMP, DUMPNL
- errorAsExternal
- _Casts_: asBoolean, asJsonValue, asString

### Integer
- abs, square, upTo, downTo 
- binaryBitwiseAnd, binaryBitwiseOr, binaryBitwiseXor (currently supported in their long form only) 
- binaryPlus, binaryMinus, binaryMultiply, binaryDivide, binaryModulo, binaryPower (+, -, *, /, %, ^)
- binaryGreaterThan, binaryGreaterThanEqual, binaryLessThan, binaryLessThanEqual (>, >=, <, <=)
- unaryMinus, unaryPlus, unaryBitwiseNot (currently supported in their long form only)
- _Casts_: asReal

### Real
- abs, square, sqrt, ln, floor, ceil, roundAsDecimal, roundAsInteger
- binaryPlus, binaryMinus, binaryMultiply, binaryDivide, binaryModulo, binaryPower (+, -, *, /, %, ^)
- binaryGreaterThan, binaryGreaterThanEqual, binaryLessThan, binaryLessThanEqual (>, >=, <, <=)
- _Casts_: asInteger

### String
- length, chunk, split, substring, substringRange 
- padLeft, padRight, toLowerCase, toUpperCase, trim, trimLeft, trimRight
- concat, concatList, reverse
- contains, startsWith, endsWith, positionOf, lastPositionOf, matchAgainstPattern, matchesRegexp
- jsonDecode
- _Casts_: asInteger, asReal

### Boolean
- binaryAnd, binaryOr, binaryXor, unaryNot (&&, ||)
- _Casts_: asInteger, asReal

### Array (immutable, returns a new Array)
- appendWith, insertAt, insertFirst, insertLast, padLeft, padRight
- length, slice, sliceRange
- combineAsString, flatten, flip, flipMap
- item, contains, countValues, indexOf, lastIndexOf
- filter, map, mapIndexValue, chainInvoke, sort, reverse, shuffle
- findFirst, findLast, min, max, sum, unique
- without, withoutAll, withoutByIndex, withoutFirst, withoutLast

### Map (immutable, returns a new Map)
- keys, values, length, item, contains
- filter, filterKeyValue, map, mapKeyValue, flip 
- keyExists, keyOf, findFirst, findFirstKeyValue
- withKeyValue, mergeWith, without, withoutAll, withoutByKey, valuesWithoutKey

### Set (immutable, returns a new Set)
- insert, without, withRemoved, &, ^, +, -
- filter, flipMap, map
- length, values
- isDisjointWith, isSubsetOf, isSupersetOf, contains 

### Type (mostly act as a Reflection-API)
- isSubtypeOf

_Type< T > for T=_
- Array, Map, Set: itemType, minLength, maxLength
- Tuple, Record: itemTypes, restType
- String: minLength, maxLength
- StringSubset: minLength, maxLength, values
- Integer, Real: minValue, maxValue
- IntegerSubset, RealSubset: minValue, maxValue, values
- Alias: typeName, aliasedType
- Atom: typeName
- Enumeration: typeName, values
- EnumerationSubset: values, enumerationType, valueWithName
- Function: parameterType, returnType
- Mutable: valueType
- Open: typeName, valueType
- Sealed: typeName, valueType
- Shape: typeName, valueType
- Result: errorType
- Type: refType

### Mutable
- value, SET
- (for Mutable< Array > only) POP, SHIFT
- (for Mutable< Array > if there is no array size constraint) PUSH, UNSHIFT
- (for Mutable< Set > only) ADD, REMOVE, CLEAR

### Function
- invoke

### Record
- with, itemValues

### Tuple
- itemValues

### Result< Nothing, T >
- error

### Sealed types
- *no methods available*

### Open types
- item, value, with

### Enumeration types
- enumeration, textValue

### JsonValue
- hydrateAs, stringify

## Additional Types

### Clock
- now

### DatabaseConnector
- execute, query

### DependencyContainer
- valueOf

### EventBus
- fire

### File
- content, createIfMissing, appendContent, replaceContent

### PasswordString
- hash, verify

### Random
- integer, uuid

### RoutePattern
- matchAgainst




