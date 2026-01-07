# 16. Core Methods

## Overview

Walnut provides a rich set of core methods (native methods) for built-in types. These methods are implemented in the runtime and are always available. This chapter documents all core methods organized by type.

## 16.1 Any Methods

These methods are available on all values, regardless of type.

### 16.1.1 Equality

**`binaryEqual` (==)** - Test equality
```walnut
Any->binaryEqual(Any => Boolean)

42 == 42;          /* true */
'hello' == 'hello'; /* true */
[1, 2] == [1, 2];  /* true */
```

**`binaryNotEqual` (!=)** - Test inequality
```walnut
Any->binaryNotEqual(Any => Boolean)

42 != 43;          /* true */
'hello' != 'world'; /* true */
```

### 16.1.2 Type Operations

**`type`** - Get the type of a value
```walnut
Any->type(Null => Type)

x = 42;
xType = x->type;  /* Type<Integer[42]> */
```

**`isOfType`** - Check if value is of a given type
```walnut
Any->isOfType(Type => Boolean)

42->isOfType(`Integer);      /* true */
42->isOfType(`String);       /* false */
'hello'->isOfType(`String);  /* true */
```

**`as`** - Cast to a different type
```walnut
Any->as(Type<T> => T)

x: Any = 42;
y = x->as(`Integer);  /* y: Integer */
```

**`shape`** - Extract shaped value
```walnut
Any->shape(Type<T> => T)

ProductId := $[id: Integer, name: String];
p = ProductId[1, 'Item'];
name = p->shape(`String);  /* 'Item' */
```

### 16.1.3 Serialization

**`jsonStringify`** - Convert to JSON string
```walnut
Any->jsonStringify(Null => String)

[1, 2, 3]->jsonStringify;        /* "[1,2,3]" */
[a: 1, b: 'hello']->jsonStringify; /* '{"a":1,"b":"hello"}' */
```

**`printed`** - Convert to human-readable string
```walnut
Any->printed(Null => String)

[1, 2, 3]->printed;          /* "[1, 2, 3]" */
[a: 1, b: 'two']->printed;   /* "[a: 1, b: 'two']" */
```

### 16.1.4 Debugging

**`DUMP`** - Print to stdout (without newline)
```walnut
Any->DUMP(Null => Null)

'Hello'->DUMP;  /* Outputs: Hello */
42->DUMP;       /* Outputs: 42 */
```

**`DUMPNL`** - Print to stdout (with newline)
```walnut
Any->DUMPNL(Null => Null)

'Hello'->DUMPNL;  /* Outputs: Hello\n */
```

**`DUMPHTMLNL`** - Print to stdout with HTML escaping and line break
```walnut
Any->DUMPHTMLNL(Null => Any)

'<div>Hello</div>'->DUMPHTMLNL;  /* Outputs: &lt;div&gt;Hello&lt;/div&gt;<br/>\n */
```

**`LOGDEBUG`** - Log to debug file
```walnut
Any->LOGDEBUG(Null => Any)

'Debug message'->LOGDEBUG;  /* Writes to log/nut.log */
```

### 16.1.5 Error Conversion

**`errorAsExternal`** - Convert error to external error
```walnut
Error<T>->errorAsExternal(String => ExternalError)

err = @'File not found';
externalErr = err->errorAsExternal('IO error');
```

### 16.1.6 Type Casts

**`asBoolean`** - Cast to Boolean
```walnut
Any->asBoolean(Null => Boolean)

42->asBoolean;      /* true */
0->asBoolean;       /* false */
'hello'->asBoolean; /* true */
''->asBoolean;      /* false */
[]->asBoolean;      /* false */
[1]->asBoolean;     /* true */
```

**`asString`** - Cast to String
```walnut
Any->asString(Null => String)

42->asString;       /* '42' */
3.14->asString;     /* '3.14' */
true->asString;     /* 'true' */
```

**`asJsonValue`** - Cast to JsonValue
```walnut
Any->asJsonValue(Null => JsonValue)

x = [a: 1, b: 'hello'];
json = x->asJsonValue;
```

**`asMutableOfType`** - Cast to mutable of given type
```walnut
Any->asMutableOfType(Type<T> => Result<Mutable<T>, CastNotAvailable>)

x = 42;
mutableInt = x->asMutableOfType(`Integer);  /* Mutable<Integer> */
```

## 16.2 Integer Methods

### 16.2.1 Arithmetic

**`binaryPlus` (+)** - Addition
```walnut
Integer->binaryPlus(Integer => Integer)

5 + 3;  /* 8 */
```

**`binaryMinus` (-)** - Subtraction
```walnut
Integer->binaryMinus(Integer => Integer)

10 - 4;  /* 6 */
```

**`binaryMultiply` (*)** - Multiplication
```walnut
Integer->binaryMultiply(Integer => Integer)

6 * 7;  /* 42 */
```

**`binaryDivide` (/)** - Division (returns Real)
```walnut
Integer->binaryDivide(Integer => Result<Real, NotANumber>)

10 / 2;  /* 5.0 */
10 / 3;  /* 3.333... */
10 / 0;  /* @NotANumber */
```

**`binaryIntegerDivide` (//)** - Integer division
```walnut
Integer->binaryIntegerDivide(Integer => Result<Integer, NotANumber>)

10 // 3;  /* 3 */
10 // 0;  /* @NotANumber */
```

**`binaryModulo` (%)** - Modulo
```walnut
Integer->binaryModulo(Integer => Result<Integer, NotANumber>)

10 % 3;  /* 1 */
10 % 0;  /* @NotANumber */
```

**`binaryPower` (**)** - Exponentiation
```walnut
Integer->binaryPower(Integer => Integer)

2 ** 8;  /* 256 */
3 ** 3;  /* 27 */
```

**`unaryPlus` (+)** - Unary plus
```walnut
Integer->unaryPlus(Null => Integer)

+42;  /* 42 */
```

**`unaryMinus` (-)** - Negation
```walnut
Integer->unaryMinus(Null => Integer)

-42;  /* -42 */
```

### 16.2.2 Comparison

**`binaryGreaterThan` (>)** - Greater than
```walnut
Integer->binaryGreaterThan(Integer => Boolean)

10 > 5;  /* true */
```

**`binaryGreaterThanEqual` (>=)** - Greater than or equal
```walnut
Integer->binaryGreaterThanEqual(Integer => Boolean)

10 >= 10;  /* true */
```

**`binaryLessThan` (<)** - Less than
```walnut
Integer->binaryLessThan(Integer => Boolean)

3 < 5;  /* true */
```

**`binaryLessThanEqual` (<=)** - Less than or equal
```walnut
Integer->binaryLessThanEqual(Integer => Boolean)

5 <= 5;  /* true */
```

### 16.2.3 Bitwise Operations

**`binaryBitwiseAnd` (&)** - Bitwise AND
```walnut
Integer->binaryBitwiseAnd(Integer => Integer)

12 & 10;  /* 8 (binary: 1100 & 1010 = 1000) */
```

**`binaryBitwiseOr` (|)** - Bitwise OR
```walnut
Integer->binaryBitwiseOr(Integer => Integer)

12 | 10;  /* 14 (binary: 1100 | 1010 = 1110) */
```

**`binaryBitwiseXor` (^)** - Bitwise XOR
```walnut
Integer->binaryBitwiseXor(Integer => Integer)

12 ^ 10;  /* 6 (binary: 1100 ^ 1010 = 0110) */
```

**`unaryBitwiseNot` (~)** - Bitwise NOT
```walnut
Integer->unaryBitwiseNot(Null => Integer)

~5;  /* -6 (two's complement) */
```

### 16.2.4 Math Functions

**`abs`** - Absolute value
```walnut
Integer->abs(Null => Integer<0..>)

(-42)->abs;  /* 42 */
```

**`square`** - Square
```walnut
Integer->square(Null => Integer<0..>)

5->square;  /* 25 */
```

### 16.2.5 Range Generation

**`upTo`** - Generate ascending range
```walnut
Integer->upTo(Integer => Array<Integer>)

2->upTo(5);  /* [2, 3, 4, 5] */
```

**`downTo`** - Generate descending range
```walnut
Integer->downTo(Integer => Array<Integer>)

5->downTo(2);  /* [5, 4, 3, 2] */
```

### 16.2.6 Type Casts

**`asReal`** - Convert to Real
```walnut
Integer->asReal(Null => Real)

42->asReal;  /* 42.0 */
```

## 16.3 Real Methods

### 16.3.1 Arithmetic

Real supports the same arithmetic operators as Integer:
- `binaryPlus` (+), `binaryMinus` (-), `binaryMultiply` (*)
- `binaryDivide` (/), `binaryModulo` (%), `binaryPower` (**)
- `unaryPlus` (+), `unaryMinus` (-)

**Examples:**
```walnut
3.14 + 2.71;   /* 5.85 */
10.5 - 3.2;    /* 7.3 */
2.5 * 4.0;     /* 10.0 */
10.0 / 3.0;    /* 3.333... */
```

### 16.3.2 Comparison

Real supports the same comparison operators as Integer:
- `binaryGreaterThan` (>), `binaryGreaterThanEqual` (>=)
- `binaryLessThan` (<), `binaryLessThanEqual` (<=)

### 16.3.3 Math Functions

**`abs`** - Absolute value
```walnut
Real->abs(Null => Real<0..>)

(-3.14)->abs;  /* 3.14 */
```

**`square`** - Square
```walnut
Real->square(Null => Real<0..>)

3.5->square;  /* 12.25 */
```

**`sqrt`** - Square root
```walnut
Real->sqrt(Null => Real<0..>)

25.0->sqrt;  /* 5.0 */
16.0->sqrt;  /* 4.0 */
```

**`ln`** - Natural logarithm
```walnut
Real->ln(Null => Real)

2.71828->ln;  /* ~1.0 */
```

**`floor`** - Floor function
```walnut
Real->floor(Null => Integer)

3.7->floor;   /* 3 */
(-3.7)->floor; /* -4 */
```

**`ceil`** - Ceiling function
```walnut
Real->ceil(Null => Integer)

3.2->ceil;    /* 4 */
(-3.2)->ceil;  /* -3 */
```

**`roundAsDecimal`** - Round to decimal places
```walnut
Real->roundAsDecimal(Integer<0..> => Real)

3.14159->roundAsDecimal(2);  /* 3.14 */
3.14159->roundAsDecimal(4);  /* 3.1416 */
```

**`roundAsInteger`** - Round to nearest integer
```walnut
Real->roundAsInteger(Null => Integer)

3.5->roundAsInteger;  /* 4 */
3.4->roundAsInteger;  /* 3 */
```

### 16.3.4 Type Casts

**`asInteger`** - Convert to Integer (truncates)
```walnut
Real->asInteger(Null => Integer)

3.14->asInteger;  /* 3 */
3.9->asInteger;   /* 3 */
```

## 16.4 String Methods

### 16.4.1 Basic Properties

**`binaryPlus` (+)** - Concatenate strings
```walnut
String->binaryPlus(String => String)

'Hello' + ' ' + 'World';  /* 'Hello World' */
```

**`binaryMultiply` (*)** - Repeat string
```walnut
String->binaryMultiply(Integer<0..> => String)

'ab' * 3;  /* 'ababab' */
'-' * 10;  /* '----------' */
```

**`length`** - Get string length
```walnut
String->length(Null => Integer<0..>)

'hello'->length;  /* 5 */
''->length;       /* 0 */
```

**`reverse`** - Reverse string
```walnut
String->reverse(Null => String)

'hello'->reverse;  /* 'olleh' */
```

### 16.4.2 Comparison

**`binaryGreaterThan` (>)** - Greater than (lexicographic)
```walnut
String->binaryGreaterThan(String => Boolean)

'b' > 'a';      /* true */
'abc' > 'ab';   /* true */
```

**`binaryGreaterThanEqual` (>=)** - Greater than or equal (lexicographic)
```walnut
String->binaryGreaterThanEqual(String => Boolean)

'b' >= 'b';     /* true */
```

**`binaryLessThan` (<)** - Less than (lexicographic)
```walnut
String->binaryLessThan(String => Boolean)

'a' < 'b';      /* true */
```

**`binaryLessThanEqual` (<=)** - Less than or equal (lexicographic)
```walnut
String->binaryLessThanEqual(String => Boolean)

'a' <= 'a';     /* true */
```

### 16.4.3 Case Conversion

**`toLowerCase`** - Convert to lowercase
```walnut
String->toLowerCase(Null => String)

'HELLO'->toLowerCase;  /* 'hello' */
```

**`toUpperCase`** - Convert to uppercase
```walnut
String->toUpperCase(Null => String)

'hello'->toUpperCase;  /* 'HELLO' */
```

### 16.4.4 HTML and Encoding

**`htmlEscape`** - HTML escape string
```walnut
String->htmlEscape(Null => String)

'<div>Hello</div>'->htmlEscape;  /* '&lt;div&gt;Hello&lt;/div&gt;' */
```

**`OUT_HTML`** - Output HTML-escaped string with nl2br
```walnut
String->OUT_HTML(Null => String)

'Line1\nLine2'->OUT_HTML;  /* Outputs: Line1<br/>Line2 (HTML-escaped) */
```

**`OUT_TXT`** - Output plain text string
```walnut
String->OUT_TXT(Null => String)

'Hello World'->OUT_TXT;  /* Outputs: Hello World */
```

### 16.4.5 Trimming

**`trim`** - Trim whitespace from both ends
```walnut
String->trim(Null => String)

'  hello  '->trim;  /* 'hello' */
```

**`trimLeft`** - Trim whitespace from left
```walnut
String->trimLeft(Null => String)

'  hello  '->trimLeft;  /* 'hello  ' */
```

**`trimRight`** - Trim whitespace from right
```walnut
String->trimRight(Null => String)

'  hello  '->trimRight;  /* '  hello' */
```

### 16.4.4 Searching

**`contains`** - Check if substring exists
```walnut
String->contains(String => Boolean)

'hello world'->contains('world');  /* true */
'hello'->contains('xyz');          /* false */
```

**`startsWith`** - Check if starts with prefix
```walnut
String->startsWith(String => Boolean)

'hello'->startsWith('hel');  /* true */
'hello'->startsWith('lo');   /* false */
```

**`endsWith`** - Check if ends with suffix
```walnut
String->endsWith(String => Boolean)

'hello'->endsWith('lo');   /* true */
'hello'->endsWith('hel');  /* false */
```

**`positionOf`** - Find first occurrence
```walnut
String->positionOf(String => Result<Integer<0..>, SubstringNotInString>)

'hello world'->positionOf('world');  /* 6 */
'hello'->positionOf('xyz');          /* @SubstringNotInString */
```

**`lastPositionOf`** - Find last occurrence
```walnut
String->lastPositionOf(String => Result<Integer<0..>, SubstringNotInString>)

'hello hello'->lastPositionOf('hello');  /* 6 */
```

**`matchAgainstPattern`** - Pattern matching
```walnut
String->matchAgainstPattern(String => Boolean)

/* Pattern matching implementation */
```

**`matchesRegexp`** - Regular expression matching
```walnut
String->matchesRegexp(RegExp => Boolean)

'hello123'->matchesRegexp(RegExp('/[a-z]+[0-9]+/'));  /* true */
```

### 16.4.5 Extraction

**`substring`** - Extract substring by start and length
```walnut
String->substring([start: Integer<0..>, length: Integer<0..>] => String)

'hello'->substring[start: 1, length: 3];  /* 'ell' */
```

**`substringRange`** - Extract substring by range
```walnut
String->substringRange([start: Integer<0..>, end: Integer<0..>] => String)

'hello'->substringRange[start: 1, end: 4];  /* 'ell' */
```

**`split`** - Split by delimiter
```walnut
String->split(String => Array<String>)

'a,b,c'->split(',');     /* ['a', 'b', 'c'] */
'hello'->split('');      /* ['h', 'e', 'l', 'l', 'o'] */
```

**`chunk`** - Split into fixed-size chunks
```walnut
String->chunk(Integer<1..> => Array<String>)

'hello'->chunk(2);  /* ['he', 'll', 'o'] */
```

### 16.4.6 Modification

**`concat`** - Concatenate strings
```walnut
String->concat(String => String)

'hello'->concat(' world');  /* 'hello world' */
```

**`concatList`** - Concatenate with array of strings
```walnut
String->concatList(Array<String> => String)

'hello'->concatList([' ', 'beautiful', ' ', 'world']);
/* 'hello beautiful world' */
```

**`padLeft`** - Pad on the left
```walnut
String->padLeft([length: Integer<0..>, padString: String] => String)

'42'->padLeft[length: 5, padString: '0'];  /* '00042' */
```

**`padRight`** - Pad on the right
```walnut
String->padRight([length: Integer<0..>, padString: String] => String)

'42'->padRight[length: 5, padString: '0'];  /* '42000' */
```

**`replace`** - Replace substring or pattern
```walnut
String->replace([match: String|RegExp, replacement: String] => String)

'hello world'->replace[match: 'world', replacement: 'there'];
/* 'hello there' */

'hello123'->replace[match: RegExp('/[0-9]+/'), replacement: 'XXX'];
/* 'helloXXX' */
```

### 16.4.7 JSON Operations

**`jsonDecode`** - Parse JSON string
```walnut
String->jsonDecode(Null => Result<JsonValue, InvalidJsonString>)

'{"a":1,"b":"hello"}'->jsonDecode;  /* JsonValue */
'invalid'->jsonDecode;              /* @InvalidJsonString */
```

### 16.4.8 Type Casts

**`asInteger`** - Parse as integer
```walnut
String->asInteger(Null => Result<Integer, NotANumber>)

'42'->asInteger;    /* 42 */
'3.14'->asInteger;  /* @NotANumber */
'abc'->asInteger;   /* @NotANumber */
```

**`asReal`** - Parse as real
```walnut
String->asReal(Null => Result<Real, NotANumber>)

'3.14'->asReal;   /* 3.14 */
'42'->asReal;     /* 42.0 */
'abc'->asReal;    /* @NotANumber */
```

## 16.5 Boolean Methods

### 16.5.1 Logical Operations

**`binaryAnd` (&&)** - Logical AND
```walnut
Boolean->binaryAnd(Boolean => Boolean)

true && true;   /* true */
true && false;  /* false */
```

**`binaryOr` (||)** - Logical OR
```walnut
Boolean->binaryOr(Boolean => Boolean)

true || false;   /* true */
false || false;  /* false */
```

**`binaryXor` (^^)** - Logical XOR
```walnut
Boolean->binaryXor(Boolean => Boolean)

true ^^ false;  /* true */
true ^^ true;   /* false */
```

**`unaryNot`** - Logical NOT
```walnut
Boolean->unaryNot(Null => Boolean)

!true;   /* false */
!false;  /* true */
```

### 16.5.2 Type Casts

**`asInteger`** - Convert to Integer
```walnut
Boolean->asInteger(Null => Integer<0..1>)

true->asInteger;   /* 1 */
false->asInteger;  /* 0 */
```

**`asReal`** - Convert to Real
```walnut
Boolean->asReal(Null => Real<0..1>)

true->asReal;   /* 1.0 */
false->asReal;  /* 0.0 */
```

## 16.6 Array Methods

All Array methods return new Arrays (immutable operations).

### 16.6.1 Basic Properties

**`length`** - Get array length
```walnut
Array->length(Null => Integer<0..>)

[1, 2, 3]->length;  /* 3 */
[]->length;         /* 0 */
```

**`item`** - Get element by index
```walnut
Array->item(Integer => Result<T, IndexOutOfRange>)

[1, 2, 3]->item(1);   /* 2 */
[1, 2, 3]->item(5);   /* @IndexOutOfRange */
```

**`contains`** - Check if element exists
```walnut
Array->contains(Any => Boolean)

[1, 2, 3]->contains(2);  /* true */
[1, 2, 3]->contains(5);  /* false */
```

### 16.6.2 Searching

**`indexOf`** - Find first index
```walnut
Array->indexOf(Any => Result<Integer<0..>, ItemNotFound>)

[1, 2, 3, 2]->indexOf(2);  /* 1 */
[1, 2, 3]->indexOf(5);     /* @ItemNotFound */
```

**`lastIndexOf`** - Find last index
```walnut
Array->lastIndexOf(Any => Result<Integer<0..>, ItemNotFound>)

[1, 2, 3, 2]->lastIndexOf(2);  /* 3 */
```

**`findFirst`** - Find first matching element
```walnut
Array<T>->findFirst(^T => Boolean => Result<T, ItemNotFound>)

[1, 2, 3, 4, 5]->findFirst(^x => Boolean :: x > 3);  /* 4 */
```

**`findLast`** - Find last matching element
```walnut
Array<T>->findLast(^T => Boolean => Result<T, ItemNotFound>)

[1, 2, 3, 4, 5]->findLast(^x => Boolean :: x > 3);  /* 5 */
```

### 16.6.3 Adding Elements

**`binaryPlus` (+)** - Concatenate arrays
```walnut
Array<T>->binaryPlus(Array<R> => Array<T|R>)

[1, 2] + [3, 4];  /* [1, 2, 3, 4] */
```

**`insertFirst`** - Insert at beginning
```walnut
Array<T>->insertFirst(T => Array<T>)

[2, 3]->insertFirst(1);  /* [1, 2, 3] */
```

**`insertLast`** - Insert at end
```walnut
Array<T>->insertLast(T => Array<T>)

[1, 2]->insertLast(3);  /* [1, 2, 3] */
```

**`insertAt`** - Insert at index
```walnut
Array<T>->insertAt([value: T, index: Integer] => Result<Array<T>, IndexOutOfRange>)

[1, 3]->insertAt[value: 2, index: 1];  /* [1, 2, 3] */
```

**`appendWith`** - Append array
```walnut
Array<T>->appendWith(Array<T> => Array<T>)

[1, 2]->appendWith([3, 4]);  /* [1, 2, 3, 4] */
```

**`PUSH`** - Push to mutable array (only for Mutable<Array>)
```walnut
Mutable<Array<T>>->PUSH(T => Null)

arr = mutable{Array<Integer>, [1, 2, 3]};
arr->PUSH(4);  /* arr->value = [1, 2, 3, 4] */
```

**`UNSHIFT`** - Unshift to mutable array (only for Mutable<Array>)
```walnut
Mutable<Array<T>>->UNSHIFT(T => Null)

arr = mutable{Array<Integer>, [2, 3]};
arr->UNSHIFT(1);  /* arr->value = [1, 2, 3] */
```

### 16.6.4 Removing Elements

**`without`** - Remove first occurrence
```walnut
Array<T>->without(T => Result<Array<T>, ItemNotFound>)

[1, 2, 3, 2]->without(2);  /* [1, 3, 2] */
```

**`withoutAll`** - Remove all occurrences
```walnut
Array<T>->withoutAll(T => Array<T>)

[1, 2, 3, 2]->withoutAll(2);  /* [1, 3] */
```

**`withoutByIndex`** - Remove by index
```walnut
Array<T>->withoutByIndex(Integer => Result<[element: T, array: Array<T>], IndexOutOfRange>)

[1, 2, 3]->withoutByIndex(1);  /* [element: 2, array: [1, 3]] */
```

**`withoutFirst`** - Remove first element
```walnut
Array<T>->withoutFirst(Null => Result<[element: T, array: Array<T>], ItemNotFound>)

[1, 2, 3]->withoutFirst;  /* [element: 1, array: [2, 3]] */
```

**`withoutLast`** - Remove last element
```walnut
Array<T>->withoutLast(Null => Result<[element: T, array: Array<T>], ItemNotFound>)

[1, 2, 3]->withoutLast;  /* [element: 3, array: [1, 2]] */
```

**`POP`** - Pop from mutable array (only for Mutable<Array>)
```walnut
Mutable<Array<T>>->POP(Null => Result<T, ItemNotFound>)

arr = mutable{Array<Integer>, [1, 2, 3]};
last = arr->POP;  /* 3, arr->value = [1, 2] */
```

**`SHIFT`** - Shift from mutable array (only for Mutable<Array>)
```walnut
Mutable<Array<T>>->SHIFT(Null => Result<T, ItemNotFound>)

arr = mutable{Array<Integer>, [1, 2, 3]};
first = arr->SHIFT;  /* 1, arr->value = [2, 3] */
```

### 16.6.5 Transformation

**`map`** - Transform elements
```walnut
Array<T>->map(^T => R => Array<R>)

[1, 2, 3]->map(^x => Integer :: x * 2);  /* [2, 4, 6] */
```

**`mapIndexValue`** - Transform with index
```walnut
Array<T>->mapIndexValue(^[index: Integer, value: T] => R => Array<R>)

['a', 'b']->mapIndexValue(^[index: Integer, value: String] => String ::
    #index->asString + ': ' + #value
);  /* ['0: a', '1: b'] */
```

**`flipMap`** - Convert to Map
```walnut
Array<T <: String>->flipMap(^T => R => Map<R>)
['a', 'bcd', 'ef']->flipMap(^String => Integer :: #->length); /* [a: 1, bcd: 3, ef: 2] */
```

**`filter`** - Filter elements
```walnut
Array<T>->filter(^T => Boolean => Array<T>)

[1, 2, 3, 4, 5]->filter(^x => Boolean :: x > 2);  /* [3, 4, 5] */
```

**`chainInvoke`** - Chain function calls
```walnut
Array<^T => T>->chainInvoke(T)

/* Apply array of functions to a value */
```

**`reverse`** - Reverse array
```walnut
Array<T>->reverse(Null => Array<T>)

[1, 2, 3]->reverse;  /* [3, 2, 1] */
```

**`sort`** - Sort array
```walnut
Array<T>->sort(Null => Array<T>)

[3, 1, 2]->sort;  /* [1, 2, 3] */
['b', 'c', 'a']->sort;  /* ['a', 'b', 'c'] */
```

**`shuffle`** - Shuffle array randomly
```walnut
Array<T>->shuffle(Null => Array<T>)

[1, 2, 3]->shuffle;  /* Random order */
```

**`unique`** - Remove duplicates
```walnut
Array<T>->unique(Null => Array<T>)

[1, 2, 2, 3, 1]->unique;  /* [1, 2, 3] */
```

**`uniqueSet`** - Convert to set
```walnut
Array<T>->uniqueSeq(Null => Set<T>)

[1, 2, 2, 3, 1]->unique;  /* [1; 2; 3] */
```

### 16.6.6 Slicing and Padding

**`slice`** - Extract slice by start and length
```walnut
Array<T>->slice([start: Integer<0..>, length: Integer<0..>] => Array<T>)

[1, 2, 3, 4, 5]->slice[start: 1, length: 3];  /* [2, 3, 4] */
```

**`sliceRange`** - Extract slice by range
```walnut
Array<T>->sliceRange([start: Integer<0..>, end: Integer<0..>] => Array<T>)

[1, 2, 3, 4, 5]->sliceRange[start: 1, end: 4];  /* [2, 3, 4] */
```

**`padLeft`** - Pad on the left
```walnut
Array<T>->padLeft([length: Integer<0..>, value: T] => Array<T>)

[3, 4]->padLeft[length: 5, value: 0];  /* [0, 0, 0, 3, 4] */
```

**`padRight`** - Pad on the right
```walnut
Array<T>->padRight([length: Integer<0..>, value: T] => Array<T>)

[1, 2]->padRight[length: 5, value: 0];  /* [1, 2, 0, 0, 0] */
```

### 16.6.7 Aggregation

**`min`** - Find minimum
```walnut
Array<T <: Real, 1..>->min(Null => T)

[3, 1, 4, 1, 5]->min;  /* 1 */
```

**`max`** - Find maximum
```walnut
Array<T <: Real, 1..>->max(Null => T)

[3, 1, 4, 1, 5]->max;  /* 5 */
```

**`sum`** - Sum elements (for numeric arrays)
```walnut
Array<T <: Real>->sum(Null => T)

[1, 2, 3, 4, 5]->sum;  /* 15 */
[1.5, 2.5, 3.0]->sum;  /* 7.0 */
```

**`countValues`** - Count occurrences
```walnut
Array<T>->countValues(Null => Map<Integer>)
T <: String or T <: Integer

[1, 2, 2, 3, 1]->countValues;  /* [1: 2, 2: 2, 3: 1] */
```

### 16.6.8 Conversion

**`combineAsString`** - Join elements
```walnut
Array<String>->combineAsString(String => String)

['a', 'b', 'c']->combineAsString(', ');  /* 'a, b, c' */
```

**`flatten`** - Flatten nested arrays
```walnut
Array<Array<T>>->flatten(Null => Array<T>)

[[1, 2], [3, 4]]->flatten;  /* [1, 2, 3, 4] */
```

**`flip`** - Convert to Map with indices as keys
```walnut
Array<T <: String>->flip(Null => Map<Integer>)

['a', 'b', 'c']->flip;  /* [0: 'a', 1: 'b', 2: 'c'] */
```

**`format`** - Format array using template string
```walnut
Array<T>->format(String => Result<String, CannotFormatString>)

['Alice', 25]->format('Name: {0}, Age: {1}');  /* 'Name: Alice, Age: 25' */
[1, 2, 3]->format('{0} + {1} = {2}');  /* '1 + 2 = 3' */
```

## 16.7 Map Methods

All Map methods return new Maps (immutable operations).

### 16.7.1 Basic Properties

**`length`** - Get map size
```walnut
Map->length(Null => Integer<0..>)

[a: 1, b: 2]->length;  /* 2 */
```

**`keys`** - Get all keys
```walnut
Map->keys(Null => Array<String>)

[a: 1, b: 2, c: 3]->keys;  /* ['a', 'b', 'c'] */
```

**`keysSet`** - Get all keys as Set
```walnut
Map->keys(Null => Set<String>)

[a: 1, b: 2, c: 3]->keysSet;  /* ['a'; 'b'; 'c'] */
```

**`values`** - Get all values
```walnut
Map<T>->values(Null => Array<T>)

[a: 1, b: 2, c: 3]->values;  /* [1, 2, 3] */
```

**`item`** - Get value by key
```walnut
Map<T>->item(String => Result<T, KeyNotFound>)

[a: 1, b: 2]->item('a');  /* 1 */
[a: 1, b: 2]->item('c');  /* @KeyNotFound */
```

**`contains`** - Check if value exists
```walnut
Map->contains(Any => Boolean)

[a: 1, b: 2]->contains(1);  /* true */
```

**`keyExists`** - Check if key exists
```walnut
Map->keyExists(String => Boolean)

[a: 1, b: 2]->keyExists('a');  /* true */
[a: 1, b: 2]->keyExists('c');  /* false */
```

### 16.7.2 Searching

**`keyOf`** - Find key by value
```walnut
Map->keyOf(Any => Result<String, ItemNotFound>)

[a: 1, b: 2]->keyOf(2);  /* 'b' */
```

**`findFirst`** - Find first matching value
```walnut
Map<T>->findFirst(^T => Boolean => Result<T, ItemNotFound>)

[a: 1, b: 2, c: 3]->findFirst(^v => Boolean :: v > 1);  /* 2 */
```

**`findFirstKeyValue`** - Find first matching key-value pair
```walnut
Map<T>->findFirstKeyValue(^[key: String, value: T] => Boolean
    => Result<[key: String, value: T], ItemNotFound>)

[a: 1, b: 2]->findFirstKeyValue(^[key: String, value: Integer] => Boolean ::
    #value > 1
);  /* [key: 'b', value: 2] */
```

### 16.7.3 Modification

**`binaryPlus` (+)** - Merge maps
```walnut
Map<T>->binaryPlus(Map<R> => Map<T|R>)

[a: 1, b: 2] + [c: 3, d: 4];  /* [a: 1, b: 2, c: 3, d: 4] */
```

**`withKeyValue`** - Add or update key-value pair
```walnut
Map<T>->withKeyValue([key: String, value: T] => Map<T>)

[a: 1]->withKeyValue[key: 'b', value: 2];  /* [a: 1, b: 2] */
```

**`mergeWith`** - Merge with another map
```walnut
Map<T>->mergeWith(Map<R> => Map<T|R>)

[a: 1, b: 2]->mergeWith[c: 3, d: 4];  /* [a: 1, b: 2, c: 3, d: 4] */
```

**`without`** - Remove first value match
```walnut
Map<T>->without(T => Result<Map<T>, ItemNotFound>)

[a: 1, b: 2]->without(2);  /* [a: 1] */
```

**`withoutAll`** - Remove all value matches
```walnut
Map<T>->withoutAll(T => Map<T>)

[a: 1, b: 2, c: 2]->withoutAll(2);  /* [a: 1] */
```

**`withoutByKey`** - Remove by key
```walnut
Map<T>->withoutByKey(String => Result<[element: T, map: Map<T>], KeyNotFound>)

[a: 1, b: 2]->withoutByKey('a');
/* [element: 1, map: [b: 2]] */
```

**`valuesWithoutKey`** - Get all values except for key
```walnut
Map<T>->valuesWithoutKey(String => Array<T>)

[a: 1, b: 2, c: 3]->valuesWithoutKey('b');  /* [1, 3] */
```

### 16.7.4 Transformation

**`map`** - Transform values
```walnut
Map<T>->map(^T => R => Map<R>)

[a: 1, b: 2]->map(^v => Integer :: v * 2);  /* [a: 2, b: 4] */
```

**`mapKeyValue`** - Transform key-value pairs
```walnut
Map<T>->mapKeyValue(^[key: String, value: T] => R => Map<R>)

[a: 1, b: 2]->mapKeyValue(^[key: String, value: Integer] => String ::
    #key + ':' + #value->asString
);  /* [a: 'a:1', b: 'b:2'] */
```

**`filter`** - Filter values
```walnut
Map<T>->filter(^T => Boolean => Map<T>)

[a: 1, b: 2, c: 3]->filter(^v => Boolean :: v > 1);  /* [b: 2, c: 3] */
```

**`filterKeyValue`** - Filter key-value pairs
```walnut
Map<T>->filterKeyValue(^[key: String, value: T] => Boolean => Map<T>)

[a: 1, b: 2]->filterKeyValue(^[key: String, value: Integer] => Boolean ::
    #key != 'a'
);  /* [b: 2] */
```

**`flip`** - Swap keys and values
```walnut
Map<String>->flip(Null => Map<String>)

[a: 'x', b: 'y']->flip;  /* [x: 'a', y: 'b'] */
```

**`format`** - Format map using template string
```walnut
Map<T>->format(String => Result<String, CannotFormatString>)

[name: 'Alice', age: 25]->format('Name: {name}, Age: {age}');
/* 'Name: Alice, Age: 25' */
```

## 16.8 Set Methods

All Set methods return new Sets (immutable operations).

### 16.8.1 Basic Properties

**`length`** - Get set size
```walnut
Set->length(Null => Integer<0..>)

[1; 2; 3]->length;  /* 3 */
```

**`values`** - Get all values as array
```walnut
Set<T>->values(Null => Array<T>)

[1; 2; 3]->values;  /* [1, 2, 3] */
```

**`contains`** - Check if element exists
```walnut
Set->contains(Any => Boolean)

[1; 2; 3]->contains(2);  /* true */
```

### 16.8.2 Modification

**`insert`** - Insert element
```walnut
Set<T>->insert(R => Set<T|R>)

[1; 2]->insert(3);  /* [1; 2; 3] */
[1; 2]->insert(1);  /* [1; 2] (no duplicates) */
```

**`without`** - Remove element
```walnut
Set<T>->without(T => Result<Set<T>, ItemNotFound>)

[1; 2; 3]->without(2);  /* [1; 3] */
```

**`withRemoved`** - Remove element (no error if not found)
```walnut
Set<T>->withRemoved(T => Set<T>)

[1; 2; 3]->withRemoved(2);  /* [1; 3] */
[1; 2; 3]->withRemoved(5);  /* [1; 2; 3] */
```

**`ADD`** - Add to mutable set (only for Mutable<Set>)
```walnut
Mutable<Set<T>>->ADD(T => Null)

s = mutable{Set<Integer>, [1; 2]};
s->ADD(3);  /* s->value = [1; 2; 3] */
```

**`REMOVE`** - Remove from mutable set (only for Mutable<Set>)
```walnut
Mutable<Set<T>>->REMOVE(T => Null)

s = mutable{Set<Integer>, [1; 2; 3]};
s->REMOVE(2);  /* s->value = [1; 3] */
```

**`CLEAR`** - Clear mutable set (only for Mutable<Set>)
```walnut
Mutable<Set<T>>->CLEAR(Null => Null)

s = mutable{Set<Integer>, [1; 2; 3]};
s->CLEAR;  /* s->value = [;] */
```

### 16.8.3 Set Operations

**`binaryBitwiseAnd` (&)** - Intersection
```walnut
Set<T>->binaryBitwiseAnd(Set<R> => Set<T&R>)

[1; 2; 3] & [2; 3; 4];  /* [2; 3] */
```

**`binaryBitwiseXor` (^)** - Symmetric difference
```walnut
Set<T>->binaryBitwiseXor(Set<R> => Set<T|R>)

[1; 2; 3] ^ [2; 3; 4];  /* [1; 4] */
```

**`binaryPlus` (+)** - Union
```walnut
Set<T>->binaryPlus(Set<R> => Set<T|R>)

[1; 2] + [2; 3];  /* [1; 2; 3] */
```

**`binaryMinus` (-)** - Difference
```walnut
Set<T>->binaryMinus(Set => Set<T>)

[1; 2; 3] - [2; 3];  /* [1] */
```

**`binaryMultiply` (*)** - Cartesian product
```walnut
Set<T, a..b>->binaryMultiply(Set<P, c..d> => Set<[T, P], a*c..b*d>)

/* Basic cartesian product */
[1; 2] * [3; 4];
/* [[1, 3]; [1, 4]; [2, 3]; [2, 4]] */

/* Single element */
['a';] * ['x';];
/* [['a', 'x'];] */

/* Mixed types */
['a'; 'b'] * [1; 2; 3];
/* [['a', 1]; ['a', 2]; ['a', 3]; ['b', 1]; ['b', 2]; ['b', 3]] */

/* Type bounds are multiplied: Set<3> * Set<3> => Set<9> */
s1 = [1; 2; 3];      /* Set<Integer, 3> */
s2 = [10; 20; 30];   /* Set<Integer, 3> */
product = s1 * s2;   /* Set<[Integer, Integer], 9> */
```

The cartesian product creates all possible pairs by combining each element from the first set with each element from the second set. The result length bounds are the product of the input bounds: `minLength1 * minLength2 .. maxLength1 * maxLength2`.

### 16.8.4 Set Comparisons

**`isDisjointWith`** - Check if disjoint
```walnut
Set->isDisjointWith(Set => Boolean)

[1; 2]->isDisjointWith([3; 4]);  /* true */
[1; 2]->isDisjointWith([2; 3]);  /* false */
```

**`isSubsetOf`** - Check if subset
```walnut
Set->isSubsetOf(Set => Boolean)

[1; 2]->isSubsetOf([1; 2; 3]);  /* true */
```

**`isSupersetOf`** - Check if superset
```walnut
Set->isSupersetOf(Set => Boolean)

[1; 2; 3]->isSupersetOf([1; 2]);  /* true */
```

### 16.8.5 Transformation

**`map`** - Transform elements
```walnut
Set<T>->map(^T => R => Set<R>)

[1; 2; 3]->map(^x => Integer :: x * 2);  /* [2; 4; 6] */
```

**`flipMap`** - Convert to Map using element values
```walnut
Set<T <: String>->flipMap(^T => R => Map<R>)
['a'; 'bcd'; 'ef']->flipMap(^String => Integer :: #->length); /* [a: 1, bcd: 3, ef: 2] */
```

**`zipMap`** - Combine set of keys with array of values into a Map
```walnut
Set<String, minL1..maxL1>->zipMap(Array<T, minL2..maxL2> => Map<String:T, min(minL1,minL2)..min(maxL1,maxL2)>)

/* Basic key-value pairing with guaranteed unique keys */
keys = ['name'; 'age'; 'city'];
values = ['Alice', 30, 'NYC'];
keys->zipMap(values);
/* [name: 'Alice', age: 30, city: 'NYC'] : Map<String|Integer> */

/* Truncates to shortest length */
['a'; 'b'; 'c']->zipMap([1, 2]);
/* [a: 1, b: 2] : Map<Integer, 2> */

/* No duplicate key issues - Set enforces uniqueness */
schema = ['id'; 'name'; 'email'];
data = [42, 'Bob', 'bob@example.com'];
schema->zipMap(data);
/* [id: 42, name: 'Bob', email: 'bob@example.com'] */

/* More precise type bounds than Array->zipMap since no duplicate keys */
```

**`flip`** - Convert to Map with elements as keys and indices as values
```walnut
Set<T <: String>->flip(Null => Map<T:Integer<0..max>, min..max>)

/* For Set<String, 2..3>, returns Map<String:Integer<0..3>, 2..3> */
['a'; 'b']->flip;  /* [a: 0, b: 1] */
['x'; 'y'; 'z']->flip;  /* [x: 0, y: 1, z: 2] */
```

- Unlike Array->flip, the returned map always has exactly as many entries as the set size, since Set elements are guaranteed to be unique.

**`filter`** - Filter elements
```walnut
Set<T>->filter(^T => Boolean => Set<T>)

[1; 2; 3; 4; 5]->filter(^x => Boolean :: x > 2);  /* [3; 4; 5] */
```

## 16.9 Tuple Methods

**`itemValues`** - Get tuple values as array
```walnut
Tuple->itemValues(Null => Array)

[1, 'hello', 3.14]->itemValues;  /* [1, 'hello', 3.14] */
```

## 16.10 Record Methods

**`with`** - Add or update field
```walnut
Record->with([key: String, value: Any] => Record)

[a: 1, b: 2]->with[key: 'c', value: 3];  /* [a: 1, b: 2, c: 3] */
```

**`itemValues`** - Get record values as map
```walnut
Record->itemValues(Null => Map)

[a: 1, b: 'hello']->itemValues;  /* [a: 1, b: 'hello'] */
```

## 16.11 Null Methods

**`asInteger`** - Convert Null to Integer
```walnut
Null->asInteger(Null => Integer)

null->asInteger;  /* 0 */
```

**`asReal`** - Convert Null to Real
```walnut
Null->asReal(Null => Real)

null->asReal;  /* 0.0 */
```

## 16.12 Mutable Methods

**`value`** - Get current value
```walnut
Mutable<T>->value(Null => T)

x = mutable{Integer, 42};
x->value;  /* 42 */
```

**`SET`** - Set new value
```walnut
Mutable<T>->SET(T => Null)

x = mutable{Integer, 42};
x->SET(100);
x->value;  /* 100 */
```

**`APPEND`** - Append to mutable collection
```walnut
Mutable<Map<T>|Array<T>>->APPEND(T => Null)

arr = mutable{Array<Integer>, [1, 2, 3]};
arr->APPEND(4);  /* arr->value = [1, 2, 3, 4] */
```

**`asInteger`** - Get mutable value as integer (for Mutable<Integer>)
```walnut
Mutable<Integer>->asInteger(Null => Integer)

x = mutable{Integer, 42};
x->asInteger;  /* 42 */
```

**`asReal`** - Get mutable value as real (for Mutable<Integer|Real>)
```walnut
Mutable<Integer|Real>->asReal(Null => Real)

x = mutable{Real, 3.14};
x->asReal;  /* 3.14 */
```

## 16.13 Function Methods

**`invoke`** - Invoke function
```walnut
^T => R->invoke(T => R)

f = ^x: Integer => Integer :: x * 2;
f->invoke(21);  /* 42 */

/* Usually written as: */
f(21);  /* 42 */
```

## 16.13 Open Type Methods

**`value`** - Get underlying value
```walnut
OpenType->value(Null => UnderlyingType)

UserId := #Integer<1..>;
id = UserId(42);
id->value;  /* 42 */
```

**`item`** - Access underlying value field
```walnut
OpenType->item(String|Integer => Any)

Point := #[x: Real, y: Real];
p = Point[x: 1.0, y: 2.0];
p->item('x');  /* 1.0 */
p->item(0);    /* 1.0 */
```

**`with`** - Update field (for record-based open types)
```walnut
OpenType->with([key: String, value: Any] => OpenType)

Point := #[x: Real, y: Real];
p = Point[x: 1.0, y: 2.0];
p2 = p->with[key: 'x', value: 3.0];  /* Point[x: 3.0, y: 2.0] */
```

## 16.14 Enumeration Type Methods

**`enumeration`** - Get enumeration type
```walnut
EnumerationValue->enumeration(Null => Type<Enumeration>)

Suit := (Clubs, Diamonds, Hearts, Spades);
card = Suit.Hearts;
card->enumeration;  /* `Suit */
```

**`textValue`** - Get text representation
```walnut
EnumerationValue->textValue(Null => String)

Suit := (Clubs, Diamonds, Hearts, Spades);
Suit.Hearts->textValue;  /* 'Hearts' */
```

## 16.15 Error Methods

**`error`** - Get error value (for Error<T> types)
```walnut
Error<T>->error(Null => T)

err = @'Something went wrong';
err->error;  /* 'Something went wrong' */
```

## 16.16 Result Methods

Methods for transforming and unwrapping Result types.

### 16.16.1 Mapping and Transformation

**`map`** - Transform the success value in a Result
```walnut
Result<T, E>->map(^T => U => Result<U, E>)

result = 42;
result->map(^# * 2);  /* 84 */

error = @'Error occurred';
error->map(^# * 2);  /* @'Error occurred' (error passes through) */

/* Works with collections inside Result */
result = [1, 2, 3];
result->map(^# * 2);  /* [2, 4, 6] */
```

**`mapIndexValue`** - Transform array elements with index
```walnut
Result<Array<T>, E>->mapIndexValue(^[index: Integer, value: T] => U => Result<Array<U>, E>)

result = ['a', 'b', 'c'];
result->mapIndexValue(^[#index, #value] :: #index->asString + ': ' + #value);
/* ['0: a', '1: b', '2: c'] */
```

**`mapKeyValue`** - Transform map/record key-value pairs
```walnut
Result<Map<T>, E>->mapKeyValue(^[key: String, value: T] => U => Result<Map<U>, E>)

result = [a: 1, b: 2, c: 3];
result->mapKeyValue(^[#key, #value] :: #key->toUpperCase + #value->asString);
/* [a: 'A1', b: 'B2', c: 'C3'] */
```

### 16.16.2 Error Handling

**`binaryOrElse` (??)** - Unwrap Result or provide fallback value
```walnut
Result<T, E>->binaryOrElse(T => T)

result = 42;
result ?? 0;  /* 42 */

error = @'Error occurred';
error ?? 0;  /* 0 (fallback value) */

/* Useful in chains */
value = getValue() ?? defaultValue;
```

**`ifError`** - Apply callback to error if present
```walnut
Result<T, E>->ifError(^E => T => T)

result = 42;
result->ifError(^err => 0);  /* 42 (no error, returns original) */

error = @'Error occurred';
error->ifError(^err => 0);  /* 0 (error callback called) */

/* With custom error handling */
result = parseInteger('abc');  /* Result<Integer, NotANumber> */
result->ifError(^err => -1);  /* -1 (fallback on parse error) */
```

## 16.17 JsonValue Methods

**`hydrateAs`** - Hydrate to type
```walnut
JsonValue->hydrateAs(Type<T> => Result<T, HydrationError>)

json = '{"a":1,"b":"hello"}'->jsonDecode;
result = json => hydrateAs(type[a: Integer, b: String]);
/* result = [a: 1, b: 'hello'] */
```

**`stringify`** - Convert to JSON string
```walnut
JsonValue->stringify(Null => String)

json->stringify;  /* '{"a":1,"b":"hello"}' */
```

## 16.18 Type Methods

Methods for introspecting and working with Type values:

**`isSubtypeOf`** - Check subtype relationship
```walnut
Type->isSubtypeOf(Type => Boolean)

`Integer->isSubtypeOf(`Real);  /* true */
```

**`typeName`**, **`minValue`**, **`maxValue`**, **`minLength`**, **`maxLength`**, **`itemType`**, **`itemTypes`**, **`parameterType`**, **`returnType`**, **`errorType`**, **`refType`**, **`restType`**, **`valueType`**, **`values`**, **`aliasedType`**, **`enumerationType`** - Type introspection methods

**`withNumberRange`**, **`withLengthRange`**, **`withItemType`**, **`withItemTypes`**, **`withParameterType`**, **`withReturnType`**, **`withErrorType`**, **`withRefType`**, **`withRestType`**, **`withValueType`**, **`withValues`**, **`withRange`** - Type construction methods

**`numberRange`**, **`valueWithName`**, **`openApiSchema`** - Additional type utilities

## 16.19 RegExp Methods

**`matchString`** - Match string against regular expression
```walnut
RegExp->matchString(String => Result<Array<String>, NoMatch>)

pattern = RegExp('/([a-z]+)([0-9]+)/');
result = pattern->matchString('hello123');  /* ['hello123', 'hello', '123'] */
```

## 16.20 Clock Methods

**`now`** - Get current timestamp
```walnut
Clock->now(Null => Real)

%clock->now;  /* Current Unix timestamp */
```

## 16.21 Random Methods

**`integer`** - Generate random integer in range
```walnut
Random->integer([min: Integer, max: Integer] => Integer)

%random->integer[min: 1, max: 100];  /* Random integer 1-100 */
```

**`uuid`** - Generate UUID
```walnut
Random->uuid(Null => String)

%random->uuid;  /* e.g., '550e8400-e29b-41d4-a716-446655440000' */
```

## 16.22 PasswordString Methods

**`hash`** - Hash password
```walnut
PasswordString->hash(Null => String)

pwd = 'secret123';
hashed = pwd->hash;  /* Bcrypt hash */
```

**`verify`** - Verify password against hash
```walnut
PasswordString->verify(String => Boolean)

pwd = 'secret123';
isValid = pwd->verify(hashedPassword);  /* true/false */
```

## 16.23 DatabaseConnector Methods

**`query`** - Execute database query
```walnut
DatabaseConnector->query([sql: String, params: Array] => Array<Map>)
```

**`execute`** - Execute database command
```walnut
DatabaseConnector->execute([sql: String, params: Array] => Integer)
```

## 16.24 File Methods

**`content`** - Read file content
```walnut
File->content(Null => Result<String, FileNotFound>)
```

**`replaceContent`** - Replace file content
```walnut
File->replaceContent(String => Null)
```

**`appendContent`** - Append to file
```walnut
File->appendContent(String => Null)
```

**`createIfMissing`** - Create file if it doesn't exist
```walnut
File->createIfMissing(Null => Null)
```

## 16.25 RoutePattern Methods

**`matchAgainst`** - Match URL against route pattern
```walnut
RoutePattern->matchAgainst(String => Result<Map<String>, NoMatch>)
```

## 16.26 DependencyContainer Methods

**`valueOf`** - Get value from dependency container
```walnut
DependencyContainer->valueOf(Type => Any)
```

## 16.27 EventBus Methods

**`fire`** - Fire event on event bus
```walnut
EventBus->fire(Any => Null)
```

## 16.28 Constructor Methods

**`asRegExp`** - Construct RegExp from string
```walnut
String->asRegExp(Null => RegExp)

'/[a-z]+/'->asRegExp;  /* RegExp */
```

**`asUuid`** - Construct UUID from string
```walnut
String->asUuid(Null => Result<Uuid, InvalidUuid>)

'550e8400-e29b-41d4-a716-446655440000'->asUuid;  /* Uuid */
```

## Summary

Walnut's core methods provide:

- **Universal operations** on Any type
- **Rich numeric operations** for Integer and Real
- **Comprehensive string manipulation**
- **Functional collection operations** (map, filter, etc.)
- **Immutable by default** with explicit mutable operations
- **Type-safe error handling** with Result types
- **JSON support** for serialization/deserialization
- **Reflection capabilities** through Type methods
- **Set operations** with mathematical semantics

All methods follow consistent patterns:
- Immutable operations return new values
- Mutable operations use UPPERCASE names
- Error cases return Result types
- Methods are discoverable through the type system
