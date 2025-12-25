# 23b. ByteArrays

## Overview

ByteArrays in Walnut represent sequences of raw bytes (non-UTF-8). They are immutable, type-safe, and support comprehensive byte manipulation operations including bitwise operations. ByteArrays can be refined with length constraints for precise domain modeling.

## 23b.1 ByteArray Type

### 23b.1.1 Type Definition

**Syntax:**
- `ByteArray` - byte arrays of any length
- `ByteArray<min..max>` - byte arrays with length from min to max bytes
- `ByteArray<min..>` - byte arrays with minimum length
- `ByteArray<..max>` - byte arrays with maximum length
- `ByteArray<length>` - byte arrays with exact length (equivalent to `ByteArray<length..length>`)

**Examples:**
```walnut
Hash256 = ByteArray<32>;        /* SHA-256 hash */
Hash512 = ByteArray<64>;        /* SHA-512 hash */
MacAddress = ByteArray<6>;      /* MAC address */
IPv4Address = ByteArray<4>;     /* IPv4 address */
AesKey128 = ByteArray<16>;      /* 128-bit AES key */
AesKey256 = ByteArray<32>;      /* 256-bit AES key */
```

**Type inference:**
```walnut
data = "\x01\x02\x03";     /* Type: ByteArray["\x01\x02\x03"], also ByteArray<3> */
empty = "";                /* Type: ByteArray[""], also ByteArray<0> */
```

### 23b.1.2 ByteArray Literals

ByteArray literals are enclosed in double quotes and can contain escape sequences for raw bytes.

**Syntax:**
```
bytearray-literal: "([^"\\]|\\[x0-9a-fA-F]{2}|\\["\\ntr])*"
```

**Examples:**
```walnut
"\x48\x65\x6c\x6c\x6f"     /* "Hello" in bytes */
"\x00\x01\x02\x03"         /* Raw bytes */
""                          /* Empty byte array */
"\xFF\xFE\xFD"             /* High bytes */
"hello"                     /* ASCII text as bytes */
"\x0A\x0D"                 /* Newline and carriage return */
```

**Escape Sequences:**
- `\"` - Double quote
- `\\` - Backslash
- `\n` - Newline byte (0x0A)
- `\r` - Carriage return byte (0x0D)
- `\t` - Tab byte (0x09)
- `\xNN` - Hexadecimal byte (NN = 00-FF)

**Examples with escape sequences:**
```walnut
"\x48\x65\x6c\x6c\x6f"     /* "Hello" */
"\xFF\x00\xFF"             /* Binary data */
"Path: C:\\Users\\Name"    /* Backslashes */
```

## 23b.2 Basic Operations

### 23b.2.1 Length

Get the number of bytes in a ByteArray.

```walnut
ByteArray->length(=> Integer<0..>)

"\x01\x02\x03"->length;    /* 3 */
""->length;                 /* 0 */
"\xFF"->length;            /* 1 */
```

### 23b.2.2 Concatenation

**Using concat method:**
```walnut
ByteArray->concat(ByteArray => ByteArray)

"\x01\x02"->concat("\x03\x04");  /* "\x01\x02\x03\x04" */
"hello "->concat("world");        /* "hello world" */
```

**Using + operator:**
```walnut
"\x01\x02" + "\x03\x04";   /* "\x01\x02\x03\x04" */
"hello " + "world";         /* "hello world" */

/* Can also append a single byte (Integer) */
"\x01\x02" + 255;          /* "\x01\x02\xFF" */
```

### 23b.2.3 Repetition

**Using * operator:**
```walnut
ByteArray * Integer<0..> => ByteArray

"\x01\x02" * 3;            /* "\x01\x02\x01\x02\x01\x02" */
"AB" * 2;                   /* "ABAB" */
"\xFF" * 0;                /* "" */
```

### 23b.2.4 Concatenate List

Concatenate multiple ByteArrays.

```walnut
ByteArray->concatList(Array<ByteArray> => ByteArray)

"\x01"->concatList["\x02", "\x03", "\x04"];  /* "\x01\x02\x03\x04" */
"hello "->concatList["world", "!"];           /* "hello world!" */
```

## 23b.3 Bitwise Operations

ByteArrays support bitwise operations on bytes. When operands have different lengths, the shorter one is automatically left-padded with zeros to match the longer one.

### 23b.3.1 Bitwise AND (&)

Perform bitwise AND on each byte pair.

```walnut
ByteArray & ByteArray => ByteArray

"\xFF\x0F" & "\x0F\xFF";   /* "\x0F\x0F" */
"\xAB\xCD" & "\x12\x34";   /* "\x02\x04" */
```

**Common use cases:**
- Masking bits
- Extracting specific bit patterns
- Clearing bits

### 23b.3.2 Bitwise OR (|)

Perform bitwise OR on each byte pair.

```walnut
ByteArray | ByteArray => ByteArray

"\x0F\x00" | "\xF0\xFF";   /* "\xFF\xFF" */
"\xAB\xCD" | "\x12\x34";   /* "\xBB\xFD" */
```

**Common use cases:**
- Setting bits
- Combining bit flags
- Merging patterns

### 23b.3.3 Bitwise XOR (^)

Perform bitwise XOR on each byte pair.

```walnut
ByteArray ^ ByteArray => ByteArray

"\xFF\x00" ^ "\x0F\xFF";   /* "\xF0\xFF" */
"\xAB\xCD" ^ "\xAB\xCD";   /* "\x00\x00" (XOR with self) */

/* XOR encryption/decryption example */
key = "\x42";
plaintext = "A";
encrypted = plaintext ^ key;     /* "\x03" */
decrypted = encrypted ^ key;     /* "A" */
```

**Common use cases:**
- Simple encryption/decryption
- Checksums
- Toggling bits
- Finding differences

### 23b.3.4 Bitwise NOT (~)

Invert all bits in each byte.

```walnut
~ByteArray => ByteArray

~"\x0F";                   /* "\xF0" */
~"\xAB\xCD";               /* "\x54\x32" */
~"\xFF\x00";               /* "\x00\xFF" */

/* Double NOT returns original */
~~"\xAB\xCD";              /* "\xAB\xCD" */
```

**Common use cases:**
- Inverting bit patterns
- Creating complement values
- Bitwise negation

## 23b.4 Search Operations

### 23b.4.1 Contains

Check if ByteArray contains a slice.

```walnut
ByteArray->contains(ByteArray => Boolean)

"\x01\x02\x03"->contains("\x02");      /* true */
"\x01\x02\x03"->contains("\x02\x03");  /* true */
"\x01\x02\x03"->contains("\x04");      /* false */
"hello"->contains("ll");                /* true */
```

### 23b.4.2 Starts With

Check if ByteArray starts with a given prefix.

```walnut
ByteArray->startsWith(ByteArray => Boolean)

"\x01\x02\x03"->startsWith("\x01");       /* true */
"\x01\x02\x03"->startsWith("\x01\x02");   /* true */
"\x01\x02\x03"->startsWith("\x02");       /* false */
"hello world"->startsWith("hello");        /* true */
```

### 23b.4.3 Ends With

Check if ByteArray ends with a given suffix.

```walnut
ByteArray->endsWith(ByteArray => Boolean)

"\x01\x02\x03"->endsWith("\x03");         /* true */
"\x01\x02\x03"->endsWith("\x02\x03");     /* true */
"\x01\x02\x03"->endsWith("\x01");         /* false */
"hello world"->endsWith("world");          /* true */
```

### 23b.4.4 Position Of

Find the first occurrence of a slice.

```walnut
ByteArray->positionOf(ByteArray => Result<Integer<0..>, SliceNotInByteArray>)

"\x01\x02\x03\x02"->positionOf("\x02");  /* 1 (first occurrence) */
"hello world"->positionOf("world");       /* 6 */
"\x01\x02\x03"->positionOf("\x04");      /* @SliceNotInByteArray */
```

### 23b.4.5 Last Position Of

Find the last occurrence of a slice.

```walnut
ByteArray->lastPositionOf(ByteArray => Result<Integer<0..>, SliceNotInByteArray>)

"\x01\x02\x03\x02"->lastPositionOf("\x02");  /* 3 (last occurrence) */
"hello hello"->lastPositionOf("hello");       /* 6 */
"\x01\x02\x03"->lastPositionOf("\x04");      /* @SliceNotInByteArray */
```

## 23b.5 Extraction and Slicing

### 23b.5.1 Substring by Start and Length

Extract a slice given a start position and length.

```walnut
ByteArray->substring([start: Integer<0..>, length: Integer<0..>] => ByteArray)

"\x01\x02\x03\x04"->substring[start: 0, length: 2];  /* "\x01\x02" */
"\x01\x02\x03\x04"->substring[start: 2, length: 2];  /* "\x03\x04" */
"hello world"->substring[start: 0, length: 5];        /* "hello" */
```

### 23b.5.2 Substring by Range

Extract a slice given start and end positions.

```walnut
ByteArray->substringRange([start: Integer<0..>, end: Integer<0..>] => ByteArray)

"\x01\x02\x03\x04"->substringRange[start: 0, end: 2];  /* "\x01\x02" */
"\x01\x02\x03\x04"->substringRange[start: 2, end: 4];  /* "\x03\x04" */
"hello world"->substringRange[start: 0, end: 5];        /* "hello" */
```

### 23b.5.3 Split

Split ByteArray by a delimiter.

```walnut
ByteArray->split(ByteArray => Array<ByteArray>)

"\x01\x00\x02\x00\x03"->split("\x00");  /* ["\x01", "\x02", "\x03"] */
"a,b,c"->split(",");                     /* ["a", "b", "c"] */
"hello world"->split(" ");               /* ["hello", "world"] */
```

### 23b.5.4 Chunk

Split ByteArray into fixed-size chunks.

```walnut
ByteArray->chunk(Integer<1..> => Array<ByteArray>)

"\x01\x02\x03\x04\x05"->chunk(2);  /* ["\x01\x02", "\x03\x04", "\x05"] */
"hello"->chunk(2);                  /* ["he", "ll", "o"] */
```

## 23b.6 Transformation

### 23b.6.1 Case Conversion

Convert ASCII letters to upper or lower case (other bytes unchanged).

```walnut
ByteArray->toUpperCase(=> ByteArray)
ByteArray->toLowerCase(=> ByteArray)

"hello"->toUpperCase;  /* "HELLO" */
"WORLD"->toLowerCase;  /* "world" */

/* Non-ASCII bytes unchanged */
"\x01hello\xFF"->toUpperCase;  /* "\x01HELLO\xFF" */
```

### 23b.6.2 Reverse

Reverse the byte order.

```walnut
ByteArray->reverse(=> ByteArray)

"\x01\x02\x03"->reverse;  /* "\x03\x02\x01" */
"hello"->reverse;          /* "olleh" */
```

### 23b.6.3 Replace

Replace occurrences of a pattern.

```walnut
ByteArray->replace([match: ByteArray, replacement: ByteArray] => ByteArray)

"\x01\x02\x01\x02"->replace[match: "\x01", replacement: "\xFF"];  /* "\xFF\x02\xFF\x02" */
"hello world"->replace[match: "world", replacement: "there"];      /* "hello there" */
```

### 23b.6.4 Trim Operations

Remove bytes from the start and/or end.

```walnut
ByteArray->trim(=> ByteArray)
ByteArray->trim(ByteArray => ByteArray)
ByteArray->trimLeft(=> ByteArray)
ByteArray->trimLeft(ByteArray => ByteArray)
ByteArray->trimRight(=> ByteArray)
ByteArray->trimRight(ByteArray => ByteArray)

"  hello  "->trim;              /* "hello" */
"\x00\x01\x00"->trim("\x00");  /* "\x01" */
"  hello"->trimLeft;            /* "hello" */
"hello  "->trimRight;           /* "hello" */
```

### 23b.6.5 Pad Operations

Pad ByteArray to a specified length.

```walnut
ByteArray->padLeft([length: Integer<0..>, padByteArray: ByteArray] => ByteArray)
ByteArray->padRight([length: Integer<0..>, padByteArray: ByteArray] => ByteArray)

"hello"->padLeft[length: 10, padByteArray: " "];      /* "     hello" */
"hello"->padRight[length: 10, padByteArray: " "];     /* "hello     " */
"\x01"->padLeft[length: 4, padByteArray: "\x00"];     /* "\x00\x00\x00\x01" */
```

## 23b.7 Comparison

ByteArrays support lexicographic comparison.

```walnut
ByteArray > ByteArray => Boolean
ByteArray >= ByteArray => Boolean
ByteArray < ByteArray => Boolean
ByteArray <= ByteArray => Boolean

"\x01\x02" < "\x01\x03";   /* true */
"\x01\x02" > "\x01\x01";   /* true */
"abc" <= "abc";             /* true */
```

## 23b.8 Conversion

### 23b.8.1 To Integer

Parse ByteArray as a decimal integer.

```walnut
ByteArray->asInteger(=> Result<Integer, NotANumber>)

"123"->asInteger;     /* 123 */
"-456"->asInteger;    /* -456 */
"abc"->asInteger;     /* @NotANumber */
```

### 23b.8.2 To Real

Parse ByteArray as a decimal real number.

```walnut
ByteArray->asReal(=> Result<Real, NotANumber>)

"3.14"->asReal;      /* 3.14 */
"123"->asReal;       /* 123 */
"-45.6"->asReal;     /* -45.6 */
"abc"->asReal;       /* @NotANumber */
```

## 23b.9 Common Use Cases

### 23b.9.1 Binary Data Processing

```walnut
/* Parse packet header */
packet = "\x01\x02\x00\x10...";
version = packet->substring[start: 0, length: 1];
type = packet->substring[start: 1, length: 1];
length = packet->substring[start: 2, length: 2];
```

### 23b.9.2 XOR Encryption/Decryption

```walnut
/* Simple XOR cipher */
encrypt = ^[plaintext: ByteArray, key: ByteArray] => ByteArray :: {
    /* Repeat key to match plaintext length */
    keyLength = key->length;
    repeatedKey = "";
    i = 0;
    ?while (i < plaintext->length) {
        repeatedKey = repeatedKey->concat(
            key->substring[start: i % keyLength, length: 1]
        );
        i = i + 1;
    };
    plaintext ^ repeatedKey
};

plaintext = "Hello, World!";
key = "\x42\x43";
encrypted = encrypt[plaintext, key];
decrypted = encrypt[encrypted, key];  /* "Hello, World!" */
```

### 23b.9.3 Bit Masking

```walnut
/* Extract lower 4 bits */
data = "\xFF";
mask = "\x0F";
result = data & mask;  /* "\x0F" */

/* Set specific bits */
data = "\x00";
bits = "\x80";
result = data | bits;  /* "\x80" */

/* Clear specific bits */
data = "\xFF";
clearMask = ~"\x0F";
result = data & clearMask;  /* "\xF0" */
```

### 23b.9.4 Checksum Calculation

```walnut
/* Simple XOR checksum */
calculateChecksum = ^data: ByteArray => Integer :: {
    checksum = 0;
    i = 0;
    ?while (i < data->length) {
        byte = data->substring[start: i, length: 1];
        /* Would need byte to integer conversion here */
        i = i + 1;
    };
    checksum
};
```

## 23b.10 Differences from String

ByteArrays differ from Strings in several key ways:

1. **Encoding**: ByteArrays are raw bytes, Strings are UTF-8
2. **Literals**: ByteArrays use `"..."`, Strings use `'...'`
3. **Operations**: ByteArrays support bitwise operations, Strings do not
4. **Length**: ByteArray length is byte count, String length is character count
5. **Case conversion**: ByteArray case operations work on ASCII only, String operations are UTF-8 aware

**Example showing the difference:**
```walnut
/* UTF-8 string */
str = 'hello';        /* UTF-8 encoded */
str->length;          /* 5 characters */

/* Byte array */
bytes = "hello";      /* Raw bytes */
bytes->length;        /* 5 bytes */

/* Bitwise operations only on ByteArray */
bytes ^ "\x42\x42\x42\x42\x42";  /* XOR operation - OK */
/* str ^ '...'; would be an error */
```
