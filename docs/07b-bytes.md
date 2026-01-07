# 23b. Bytes

## Overview

Bytes in Walnut represent sequences of raw bytes (non-UTF-8). They are immutable, type-safe, and support comprehensive byte manipulation operations including bitwise operations. Bytes can be refined with length constraints for precise domain modeling.

## 23b.1 Bytes Type

### 23b.1.1 Type Definition

**Syntax:**
- `Bytes` - byte arrays of any length
- `Bytes<min..max>` - byte arrays with length from min to max bytes
- `Bytes<min..>` - byte arrays with minimum length
- `Bytes<..max>` - byte arrays with maximum length
- `Bytes<length>` - byte arrays with exact length (equivalent to `Bytes<length..length>`)

**Examples:**
```walnut
Hash256 = Bytes<32>;        /* SHA-256 hash */
Hash512 = Bytes<64>;        /* SHA-512 hash */
MacAddress = Bytes<6>;      /* MAC address */
IPv4Address = Bytes<4>;     /* IPv4 address */
AesKey128 = Bytes<16>;      /* 128-bit AES key */
AesKey256 = Bytes<32>;      /* 256-bit AES key */
```

**Type inference:**
```walnut
data = "\x01\x02\x03";     /* Type: Bytes["\x01\x02\x03"], also Bytes<3> */
empty = "";                /* Type: Bytes[""], also Bytes<0> */
```

### 23b.1.2 Bytes Literals

Bytes literals are enclosed in double quotes and can contain escape sequences for raw bytes.

**Syntax:**
```
bytes-literal: "([^"\\]|\\[x0-9a-fA-F]{2}|\\["\\ntr])*"
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

Get the number of bytes in a Bytes.

```walnut
Bytes->length(=> Integer<0..>)

"\x01\x02\x03"->length;    /* 3 */
""->length;                 /* 0 */
"\xFF"->length;            /* 1 */
```

### 23b.2.2 Concatenation

**Using concat method:**
```walnut
Bytes->concat(Bytes => Bytes)

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
Bytes * Integer<0..> => Bytes

"\x01\x02" * 3;            /* "\x01\x02\x01\x02\x01\x02" */
"AB" * 2;                   /* "ABAB" */
"\xFF" * 0;                /* "" */
```

### 23b.2.4 Concatenate List

Concatenate multiple Bytes.

```walnut
Bytes->concatList(Array<Bytes> => Bytes)

"\x01"->concatList["\x02", "\x03", "\x04"];  /* "\x01\x02\x03\x04" */
"hello "->concatList["world", "!"];           /* "hello world!" */
```

## 23b.3 Bitwise Operations

Bytes support bitwise operations on bytes. When operands have different lengths, the shorter one is automatically left-padded with zeros to match the longer one.

### 23b.3.1 Bitwise AND (&)

Perform bitwise AND on each byte pair.

```walnut
Bytes & Bytes => Bytes

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
Bytes | Bytes => Bytes

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
Bytes ^ Bytes => Bytes

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
~Bytes => Bytes

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

Check if Bytes contains a slice.

```walnut
Bytes->contains(Bytes => Boolean)

"\x01\x02\x03"->contains("\x02");      /* true */
"\x01\x02\x03"->contains("\x02\x03");  /* true */
"\x01\x02\x03"->contains("\x04");      /* false */
"hello"->contains("ll");                /* true */
```

### 23b.4.2 Starts With

Check if Bytes starts with a given prefix.

```walnut
Bytes->startsWith(Bytes => Boolean)

"\x01\x02\x03"->startsWith("\x01");       /* true */
"\x01\x02\x03"->startsWith("\x01\x02");   /* true */
"\x01\x02\x03"->startsWith("\x02");       /* false */
"hello world"->startsWith("hello");        /* true */
```

### 23b.4.3 Ends With

Check if Bytes ends with a given suffix.

```walnut
Bytes->endsWith(Bytes => Boolean)

"\x01\x02\x03"->endsWith("\x03");         /* true */
"\x01\x02\x03"->endsWith("\x02\x03");     /* true */
"\x01\x02\x03"->endsWith("\x01");         /* false */
"hello world"->endsWith("world");          /* true */
```

### 23b.4.4 Position Of

Find the first occurrence of a slice.

```walnut
Bytes->positionOf(Bytes => Result<Integer<0..>, SliceNotInBytes>)

"\x01\x02\x03\x02"->positionOf("\x02");  /* 1 (first occurrence) */
"hello world"->positionOf("world");       /* 6 */
"\x01\x02\x03"->positionOf("\x04");      /* @SliceNotInBytes */
```

### 23b.4.5 Last Position Of

Find the last occurrence of a slice.

```walnut
Bytes->lastPositionOf(Bytes => Result<Integer<0..>, SliceNotInBytes>)

"\x01\x02\x03\x02"->lastPositionOf("\x02");  /* 3 (last occurrence) */
"hello hello"->lastPositionOf("hello");       /* 6 */
"\x01\x02\x03"->lastPositionOf("\x04");      /* @SliceNotInBytes */
```

## 23b.5 Extraction and Slicing

### 23b.5.1 Substring by Start and Length

Extract a slice given a start position and length.

```walnut
Bytes->substring([start: Integer<0..>, length: Integer<0..>] => Bytes)

"\x01\x02\x03\x04"->substring[start: 0, length: 2];  /* "\x01\x02" */
"\x01\x02\x03\x04"->substring[start: 2, length: 2];  /* "\x03\x04" */
"hello world"->substring[start: 0, length: 5];        /* "hello" */
```

### 23b.5.2 Substring by Range

Extract a slice given start and end positions.

```walnut
Bytes->substringRange([start: Integer<0..>, end: Integer<0..>] => Bytes)

"\x01\x02\x03\x04"->substringRange[start: 0, end: 2];  /* "\x01\x02" */
"\x01\x02\x03\x04"->substringRange[start: 2, end: 4];  /* "\x03\x04" */
"hello world"->substringRange[start: 0, end: 5];        /* "hello" */
```

### 23b.5.3 Split

Split Bytes by a delimiter.

```walnut
Bytes->split(Bytes => Array<Bytes>)

"\x01\x00\x02\x00\x03"->split("\x00");  /* ["\x01", "\x02", "\x03"] */
"a,b,c"->split(",");                     /* ["a", "b", "c"] */
"hello world"->split(" ");               /* ["hello", "world"] */
```

### 23b.5.4 Chunk

Split Bytes into fixed-size chunks.

```walnut
Bytes->chunk(Integer<1..> => Array<Bytes>)

"\x01\x02\x03\x04\x05"->chunk(2);  /* ["\x01\x02", "\x03\x04", "\x05"] */
"hello"->chunk(2);                  /* ["he", "ll", "o"] */
```

## 23b.6 Transformation

### 23b.6.1 Case Conversion

Convert ASCII letters to upper or lower case (other bytes unchanged).

```walnut
Bytes->toUpperCase(=> Bytes)
Bytes->toLowerCase(=> Bytes)

"hello"->toUpperCase;  /* "HELLO" */
"WORLD"->toLowerCase;  /* "world" */

/* Non-ASCII bytes unchanged */
"\x01hello\xFF"->toUpperCase;  /* "\x01HELLO\xFF" */
```

### 23b.6.2 Reverse

Reverse the byte order.

```walnut
Bytes->reverse(=> Bytes)

"\x01\x02\x03"->reverse;  /* "\x03\x02\x01" */
"hello"->reverse;          /* "olleh" */
```

### 23b.6.3 Replace

Replace occurrences of a pattern.

```walnut
Bytes->replace([match: Bytes, replacement: Bytes] => Bytes)

"\x01\x02\x01\x02"->replace[match: "\x01", replacement: "\xFF"];  /* "\xFF\x02\xFF\x02" */
"hello world"->replace[match: "world", replacement: "there"];      /* "hello there" */
```

### 23b.6.4 Trim Operations

Remove bytes from the start and/or end.

```walnut
Bytes->trim(=> Bytes)
Bytes->trim(Bytes => Bytes)
Bytes->trimLeft(=> Bytes)
Bytes->trimLeft(Bytes => Bytes)
Bytes->trimRight(=> Bytes)
Bytes->trimRight(Bytes => Bytes)

"  hello  "->trim;              /* "hello" */
"\x00\x01\x00"->trim("\x00");  /* "\x01" */
"  hello"->trimLeft;            /* "hello" */
"hello  "->trimRight;           /* "hello" */
```

### 23b.6.5 Pad Operations

Pad Bytes to a specified length.

```walnut
Bytes->padLeft([length: Integer<0..>, padBytes: Bytes] => Bytes)
Bytes->padRight([length: Integer<0..>, padBytes: Bytes] => Bytes)

"hello"->padLeft[length: 10, padBytes: " "];      /* "     hello" */
"hello"->padRight[length: 10, padBytes: " "];     /* "hello     " */
"\x01"->padLeft[length: 4, padBytes: "\x00"];     /* "\x00\x00\x00\x01" */
```

## 23b.7 Comparison

Bytes support lexicographic comparison.

```walnut
Bytes > Bytes => Boolean
Bytes >= Bytes => Boolean
Bytes < Bytes => Boolean
Bytes <= Bytes => Boolean

"\x01\x02" < "\x01\x03";   /* true */
"\x01\x02" > "\x01\x01";   /* true */
"abc" <= "abc";             /* true */
```

## 23b.8 Conversion

### 23b.8.1 To Integer

Parse Bytes as a decimal integer.

```walnut
Bytes->asInteger(=> Result<Integer, NotANumber>)

"123"->asInteger;     /* 123 */
"-456"->asInteger;    /* -456 */
"abc"->asInteger;     /* @NotANumber */
```

### 23b.8.2 To Real

Parse Bytes as a decimal real number.

```walnut
Bytes->asReal(=> Result<Real, NotANumber>)

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
encrypt = ^[plaintext: Bytes, key: Bytes] => Bytes :: {
    /* Repeat key to match plaintext length */
    keyLength = key->length;
    repeatedKey = mutable{Bytes, ""};
    i = mutable{Integer, 0};
    ?while (i->value < plaintext->length) {
        repeatedKey->SET(repeatedKey->value->concat(
            key->substring[start: i->value % keyLength, length: 1]
        ));
        i->SET(i->value + 1);
    };
    plaintext ^ repeatedKey->value
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
calculateChecksum = ^data: Bytes => Integer :: {
    checksum = mutable{Integer, 0};
    i = mutable{Integer, 0};
    ?while (i->value < data->length) {
        byte = data->substring[start: i->value, length: 1];
        /* Would need byte to integer conversion here */
        i->SET(i->value + 1);
    };
    checksum->value
};
```

## 23b.10 Differences from String

Bytes differ from Strings in several key ways:

1. **Encoding**: Bytes are raw bytes, Strings are UTF-8
2. **Literals**: Bytes use `"..."`, Strings use `'...'`
3. **Operations**: Bytes support bitwise operations, Strings do not
4. **Length**: Bytes length is byte count, String length is character count
5. **Case conversion**: Bytes case operations work on ASCII only, String operations are UTF-8 aware

**Example showing the difference:**
```walnut
/* UTF-8 string */
str = 'hello';        /* UTF-8 encoded */
str->length;          /* 5 characters */

/* Byte array */
bytes = "hello";      /* Raw bytes */
bytes->length;        /* 5 bytes */

/* Bitwise operations only on Bytes */
bytes ^ "\x42\x42\x42\x42\x42";  /* XOR operation - OK */
/* str ^ '...'; would be an error */
```
