# 23. Strings

## Overview

Strings in Walnut represent sequences of UTF-8 characters. They are immutable, type-safe, and support comprehensive manipulation operations. Strings can be refined with length constraints and value subsets for precise domain modeling.

## 23.1 String Type

### 23.1.1 Type Definition

**Syntax:**
- `String` - strings of any length
- `String<min..max>` - strings with length from min to max
- `String<min..>` - strings with minimum length
- `String<..max>` - strings with maximum length
- `String<length>` - strings with exact length (equivalent to `String<length..length>`)

**Examples:**
```walnut
Email = String<5..254>;
Password = String<8..>;
ProductName = String<1..100>;
CountryCode = String<2>;
NonEmptyString = String<1..>;
ShortString = String<..10>;
```

**Type inference:**
```walnut
greeting = 'Hello';    /* Type: String['Hello'], also String<5..5> */
empty = '';            /* Type: String[''], also String<0..0> */
name = 'Alice';        /* Type: String['Alice'], also String<5..5> */
```

### 23.1.2 String Literals

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
'Line 1\nLine 2'        /* Newline */
```

**Escape Sequences:**
- `\'` - Single quote
- `\\` - Backslash
- `\n` - Newline
- `\r` - Carriage return
- `\t` - Tab

**Examples with escape sequences:**
```walnut
'Line 1\nLine 2'        /* Multi-line text */
'Tab\there'             /* Tab character */
'Backslash: \\'         /* Backslash character */
'Quote: \''             /* Single quote */
'Path: C:\\Users\\Name' /* Windows path */
```

### 23.1.3 String Subsets

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

direction = 'north';  /* Type: String['north'], can be assigned to Direction */
```

**Combined with length constraints:**
String subsets automatically have length constraints based on their values:

```walnut
Direction = String['north', 'south', 'east', 'west'];
/* All values have length between 4 and 5 */
/* So Direction <: String<4..5> */
```

## 23.2 String Properties

### 23.2.1 Length

Get the length of a string (UTF-8 character count).

```walnut
String->length(Null => Integer<0..>)

'hello'->length;       /* 5 */
''->length;            /* 0 */
'Hello, World!'->length; /* 13 */
```

### 23.2.2 Reverse

Reverse the characters in a string.

```walnut
String->reverse(Null => String)

'hello'->reverse;      /* 'olleh' */
'racecar'->reverse;    /* 'racecar' */
```

## 23.3 Case Conversion

### 23.3.1 To Lowercase

Convert all characters to lowercase.

```walnut
String->toLowerCase(Null => String)

'HELLO'->toLowerCase;       /* 'hello' */
'Hello World'->toLowerCase; /* 'hello world' */
'ABC123'->toLowerCase;      /* 'abc123' */
```

### 23.3.2 To Uppercase

Convert all characters to uppercase.

```walnut
String->toUpperCase(Null => String)

'hello'->toUpperCase;       /* 'HELLO' */
'Hello World'->toUpperCase; /* 'HELLO WORLD' */
'abc123'->toUpperCase;      /* 'ABC123' */
```

## 23.4 Trimming

### 23.4.1 Trim Both Ends

Remove whitespace from both ends of the string.

```walnut
String->trim(Null => String)

'  hello  '->trim;     /* 'hello' */
'\n\thello\t\n'->trim; /* 'hello' */
'  '->trim;            /* '' */
```

### 23.4.2 Trim Left

Remove whitespace from the left side only.

```walnut
String->trimLeft(Null => String)

'  hello  '->trimLeft;  /* 'hello  ' */
'\thello'->trimLeft;    /* 'hello' */
```

### 23.4.3 Trim Right

Remove whitespace from the right side only.

```walnut
String->trimRight(Null => String)

'  hello  '->trimRight; /* '  hello' */
'hello\n'->trimRight;   /* 'hello' */
```

## 23.5 Searching

### 23.5.1 Contains

Check if a substring exists in the string.

```walnut
String->contains(String => Boolean)

'hello world'->contains('world');  /* true */
'hello world'->contains('xyz');    /* false */
'hello'->contains('');             /* true (empty string is in all strings) */
```

### 23.5.2 Starts With

Check if string starts with a given prefix.

```walnut
String->startsWith(String => Boolean)

'hello world'->startsWith('hello'); /* true */
'hello world'->startsWith('world'); /* false */
'hello'->startsWith('');            /* true */
```

### 23.5.3 Ends With

Check if string ends with a given suffix.

```walnut
String->endsWith(String => Boolean)

'hello world'->endsWith('world');  /* true */
'hello world'->endsWith('hello');  /* false */
'hello'->endsWith('');             /* true */
```

### 23.5.4 Position Of

Find the first occurrence of a substring.

```walnut
String->positionOf(String => Result<Integer<0..>, SubstringNotInString>)

'hello world'->positionOf('world');  /* 6 */
'hello world'->positionOf('l');      /* 2 (first 'l') */
'hello'->positionOf('xyz');          /* @SubstringNotInString */
'hello'->positionOf('');             /* 0 (empty string at start) */
```

### 23.5.5 Last Position Of

Find the last occurrence of a substring.

```walnut
String->lastPositionOf(String => Result<Integer<0..>, SubstringNotInString>)

'hello hello'->lastPositionOf('hello');  /* 6 */
'hello world'->lastPositionOf('l');      /* 9 (last 'l') */
'hello'->lastPositionOf('xyz');          /* @SubstringNotInString */
```

### 23.5.6 Matches Regular Expression

Check if string matches a regular expression pattern.

```walnut
String->matchesRegexp(RegExp => Boolean)

'hello123'->matchesRegexp(RegExp('/[a-z]+[0-9]+/'));  /* true */
'test@example.com'->matchesRegexp(RegExp('/.*@.*/'));  /* true */
'abc'->matchesRegexp(RegExp('/[0-9]+/'));              /* false */
```

## 23.6 Extraction and Slicing

### 23.6.1 Substring by Start and Length

Extract a substring given a start position and length.

```walnut
String->substring([start: Integer<0..>, length: Integer<0..>] => String)

'hello world'->substring[start: 0, length: 5];  /* 'hello' */
'hello world'->substring[start: 6, length: 5];  /* 'world' */
'hello'->substring[start: 1, length: 3];        /* 'ell' */
'hello'->substring[start: 0, length: 0];        /* '' */
```

### 23.6.2 Substring by Range

Extract a substring given start and end positions.

```walnut
String->substringRange([start: Integer<0..>, end: Integer<0..>] => String)

'hello world'->substringRange[start: 0, end: 5];  /* 'hello' */
'hello world'->substringRange[start: 6, end: 11]; /* 'world' */
'hello'->substringRange[start: 1, end: 4];        /* 'ell' */
```

### 23.6.3 Split

Split string by a delimiter.

```walnut
String->split(String => Array<String>)

'a,b,c'->split(',');        /* ['a', 'b', 'c'] */
'hello world'->split(' ');  /* ['hello', 'world'] */
'hello'->split('');         /* ['h', 'e', 'l', 'l', 'o'] */
'a,,b'->split(',');         /* ['a', '', 'b'] */
```

### 23.6.4 Chunk

Split string into fixed-size chunks.

```walnut
String->chunk(Integer<1..> => Array<String>)

'hello'->chunk(2);    /* ['he', 'll', 'o'] */
'abcdefgh'->chunk(3); /* ['abc', 'def', 'gh'] */
'hello'->chunk(1);    /* ['h', 'e', 'l', 'l', 'o'] */
```

## 23.7 Modification

### 23.7.1 Concatenation

Concatenate two strings.

```walnut
String->concat(String => String)

'hello'->concat(' world');  /* 'hello world' */
'foo'->concat('bar');       /* 'foobar' */
''->concat('hello');        /* 'hello' */
```

**Operator form:**
```walnut
'hello' + ' world';  /* 'hello world' */
```

### 23.7.2 Concatenate List

Concatenate a string with an array of strings.

```walnut
String->concatList(Array<String> => String)

'hello'->concatList([' ', 'beautiful', ' ', 'world']);
/* 'hello beautiful world' */

'start'->concatList([' middle', ' end']);
/* 'start middle end' */
```

### 23.7.3 Pad Left

Pad a string on the left to a specified length.

```walnut
String->padLeft([length: Integer<0..>, padString: String] => String)

'42'->padLeft[length: 5, padString: '0'];      /* '00042' */
'hello'->padLeft[length: 10, padString: ' '];  /* '     hello' */
'abc'->padLeft[length: 5, padString: 'xy'];    /* 'xyabc' */
```

### 23.7.4 Pad Right

Pad a string on the right to a specified length.

```walnut
String->padRight([length: Integer<0..>, padString: String] => String)

'42'->padRight[length: 5, padString: '0'];     /* '42000' */
'hello'->padRight[length: 10, padString: ' ']; /* 'hello     ' */
'abc'->padRight[length: 5, padString: 'xy'];   /* 'abcxy' */
```

### 23.7.5 Replace

Replace substring or pattern with replacement.

```walnut
String->replace([match: String|RegExp, replacement: String] => String)

/* Replace string */
'hello world'->replace[match: 'world', replacement: 'there'];
/* 'hello there' */

'foo bar foo'->replace[match: 'foo', replacement: 'baz'];
/* 'baz bar foo' (only first occurrence) */

/* Replace with regex */
'hello123world456'->replace[match: RegExp('/[0-9]+/'), replacement: 'XXX'];
/* 'helloXXXworld456' */
```

## 23.8 JSON Operations

### 23.8.1 Parse JSON

Parse a JSON string into a JsonValue.

```walnut
String->jsonDecode(Null => Result<JsonValue, InvalidJsonString>)

'{"a":1,"b":"hello"}'->jsonDecode;  /* JsonValue */
'[1,2,3]'->jsonDecode;              /* JsonValue */
'invalid'->jsonDecode;              /* @InvalidJsonString */
'true'->jsonDecode;                 /* JsonValue (boolean) */
```

## 23.9 Casting From String

### 23.9.1 To Integer

Parse string as integer.

```walnut
String->asInteger(Null => Result<Integer, NotANumber>)

'42'->asInteger;     /* 42 */
'-17'->asInteger;    /* -17 */
'0'->asInteger;      /* 0 */
'3.14'->asInteger;   /* @NotANumber */
'abc'->asInteger;    /* @NotANumber */
''->asInteger;       /* @NotANumber */
'  42  '->asInteger; /* @NotANumber (whitespace not allowed) */
```

### 23.9.2 To Real

Parse string as real number.

```walnut
String->asReal(Null => Result<Real, NotANumber>)

'3.14'->asReal;    /* 3.14 */
'42'->asReal;      /* 42.0 */
'-2.5'->asReal;    /* -2.5 */
'0.0'->asReal;     /* 0.0 */
'abc'->asReal;     /* @NotANumber */
''->asReal;        /* @NotANumber */
```

### 23.9.3 To Boolean

Convert string to boolean.

```walnut
String->asBoolean(Null => Boolean)

'hello'->asBoolean;  /* true (non-empty string) */
''->asBoolean;       /* false (empty string) */
'false'->asBoolean;  /* true (non-empty string, not parsed) */
```

## 23.10 Casting To String

### 23.10.1 From Integer

```walnut
Integer->asString(Null => String)

42->asString;      /* '42' */
(-17)->asString;   /* '-17' */
0->asString;       /* '0' */
```

### 23.10.2 From Real

```walnut
Real->asString(Null => String)

3.14->asString;    /* '3.14' */
(-2.71)->asString; /* '-2.71' */
100.0->asString;   /* '100.0' or '100' */
```

### 23.10.3 From Boolean

```walnut
Boolean->asString(Null => String)

true->asString;   /* 'true' */
false->asString;  /* 'false' */
```

### 23.10.4 From Any

The universal `asString` method works on any value.

```walnut
Any->asString(Null => String)

42->asString;                  /* '42' */
3.14->asString;                /* '3.14' */
true->asString;                /* 'true' */
[1, 2, 3]->asString;           /* '[1, 2, 3]' (or similar) */
[a: 1, b: 2]->asString;        /* '[a: 1, b: 2]' (or similar) */
```

## 23.11 Practical Examples

### 23.11.1 Email Validation

```walnut
Email = String<5..254>;

validateEmail = ^email: String => Result<Email, String> :: {
    trimmed = email->trim->toLowerCase;

    ?when(trimmed->length < 5 || trimmed->length > 254) {
        @'Email must be between 5 and 254 characters'
    } ~ ?when(!trimmed->contains('@')) {
        @'Email must contain @'
    } ~ ?when(!trimmed->contains('.')) {
        @'Email must contain a domain'
    } ~ {
        trimmed
    }
};

result1 = validateEmail('user@example.com');  /* OK */
result2 = validateEmail('invalid');           /* Error */
```

### 23.11.2 Password Strength

```walnut
Password = String<8..>;

checkPasswordStrength = ^password: String => Result<Password, String> :: {
    ?when(password->length < 8) {
        @'Password must be at least 8 characters'
    } ~ ?when(!password->matchesRegexp(RegExp('/[A-Z]/'))) {
        @'Password must contain uppercase letter'
    } ~ ?when(!password->matchesRegexp(RegExp('/[a-z]/'))) {
        @'Password must contain lowercase letter'
    } ~ ?when(!password->matchesRegexp(RegExp('/[0-9]/'))) {
        @'Password must contain digit'
    } ~ {
        password
    }
};

result1 = checkPasswordStrength('SecurePass123');  /* OK */
result2 = checkPasswordStrength('weak');           /* Error */
```

### 23.11.3 URL Parsing

```walnut
/* Simple URL parser */
parseUrl = ^url: String => Result<[protocol: String, domain: String, path: String], String> :: {
    ?when(!url->contains('://')) {
        @'Invalid URL format'
    } ~ {
        parts = url->split('://');
        protocol = ?whenIsError(parts->item(0)) { '' };
        rest = ?whenIsError(parts->item(1)) { '' };

        ?when(rest->contains('/')) {
            domainPath = rest->split('/');
            domain = ?whenIsError(domainPath->item(0)) { '' };
            pathParts = domainPath->slice[start: 1, length: domainPath->length - 1];
            path = '/' + pathParts->combineAsString('/');
            [protocol: protocol, domain: domain, path: path]
        } ~ {
            [protocol: protocol, domain: rest, path: '/']
        }
    }
};

result = parseUrl('https://example.com/path/to/page');
/* [protocol: 'https', domain: 'example.com', path: '/path/to/page'] */
```

### 23.11.4 Text Formatting

```walnut
/* Format name as "Last, First" */
formatName = ^[firstName: String, lastName: String] => String :: {
    #lastName->trim + ', ' + #firstName->trim
};

/* Title case */
titleCase = ^text: String => String :: {
    words = text->toLowerCase->split(' ');
    words->map(^word => String :: {
        ?when(word->length > 0) {
            first = word->substring[start: 0, length: 1]->toUpperCase;
            rest = word->substring[start: 1, length: word->length - 1];
            first + rest
        } ~ {
            word
        }
    })->combineAsString(' ')
};

formatted = titleCase('hello world');  /* 'Hello World' */
```

### 23.11.5 String Builder Pattern

```walnut
/* Build CSV row */
buildCsvRow = ^columns: Array<String> => String :: {
    columns->map(^col => String :: {
        /* Escape quotes and wrap in quotes if needed */
        ?when(col->contains(',') || col->contains('"') || col->contains('\n')) {
            escaped = col->replace[match: '"', replacement: '""'];
            '"' + escaped + '"'
        } ~ {
            col
        }
    })->combineAsString(',')
};

row = buildCsvRow(['John Doe', 'john@example.com', 'New York, NY']);
/* 'John Doe,john@example.com,"New York, NY"' */
```

### 23.11.6 Template String Replacement

```walnut
/* Simple template replacement */
fillTemplate = ^[template: String, values: Map<String>] => String :: {
    result = mutable{String, #template};

    #values->keys->forEach(^key :: {
        placeholder = '{{' + key + '}}';
        value = ?whenIsError(#values->item(key)) { '' };
        current = result->value;
        ?when(current->contains(placeholder)) {
            replaced = current->replace[match: placeholder, replacement: value];
            result->SET(replaced)
        }
    });

    result->value
};

template = 'Hello {{name}}, your balance is ${{balance}}';
filled = fillTemplate([template: template, values: [name: 'Alice', balance: '100.50']]);
/* 'Hello Alice, your balance is $100.50' */
```

### 23.11.7 Slugify

```walnut
/* Convert string to URL-friendly slug */
slugify = ^text: String => String :: {
    text->toLowerCase
        ->trim
        ->replace[match: RegExp('/[^a-z0-9]+/'), replacement: '-']
        ->trim  /* Remove leading/trailing dashes */
};

slug = slugify('Hello World! This is a Test.');
/* 'hello-world-this-is-a-test' */
```

## 23.12 Best Practices

### 23.12.1 Use String Subsets for Fixed Values

```walnut
/* Good: String subset for fixed values */
Status = String['pending', 'active', 'completed'];

/* Avoid: Plain strings */
/* status: String */
```

### 23.12.2 Use Length Constraints

```walnut
/* Good: Constrain string length */
Username = String<3..20>;
Email = String<5..254>;
Tweet = String<1..280>;

/* Avoid: Unconstrained strings */
/* username: String */
```

### 23.12.3 Handle Parse Errors

```walnut
/* Good: Handle parse errors */
parseInteger = ^text: String => Result<Integer, String> :: {
    text->asInteger @ err :: 'Invalid integer: ' + text
};

/* Or use pattern matching */
result = text->asInteger;
?whenTypeOf(result) {
    `Integer: result->printed,
    `NotANumber: 'Parse error'
};
```

### 23.12.4 Validate User Input

```walnut
/* Good: Validate before use */
sanitizeInput = ^input: String => String :: {
    input->trim
        ->replace[match: RegExp('/<script>/'), replacement: '']
        ->substring[start: 0, length: 1000]  /* Limit length */
};
```

### 23.12.5 Use Immutable Operations

```walnut
/* Good: Immutable string operations */
original = 'hello';
upper = original->toUpperCase;  /* 'HELLO', original unchanged */

/* Strings are always immutable in Walnut */
```

### 23.12.6 Prefer Explicit Concatenation

```walnut
/* Good: Clear concatenation */
fullName = firstName + ' ' + lastName;

/* Also good: Using concat method */
fullName = firstName->concat(' ')->concat(lastName);
```

### 23.12.7 Handle Empty Strings

```walnut
/* Good: Check for empty strings */
NonEmptyString = String<1..>;

validateName = ^name: String => Result<NonEmptyString, String> :: {
    trimmed = name->trim;
    ?when(trimmed->length == 0) {
        @'Name cannot be empty'
    } ~ {
        trimmed
    }
};
```

## Summary

Walnut's String type provides:

- **Immutable operations** - all string methods return new strings
- **UTF-8 encoding** - full Unicode support
- **Length constraints** - `String<min..max>` for domain modeling
- **Value subsets** - `String['value1', 'value2']` for fixed sets
- **Rich operations** - searching, extraction, modification, formatting
- **Type-safe parsing** - Result types for conversions
- **Pattern matching** - regular expression support
- **JSON support** - parse and stringify JSON data

Key features:
- All strings are immutable
- Length is measured in UTF-8 characters
- Escape sequences for special characters
- Comprehensive search and manipulation methods
- Type-safe conversions with Result types
- Efficient concatenation and formatting

String operations are designed for safety and expressiveness, making text processing both powerful and type-safe.
