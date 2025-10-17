# 1. Lexical Structure

## 1.1 Source Files

Walnut source files use the `.nut` extension. Special file types include:
- `.test.nut` - Test files containing test cases
- `.nut.html` - Template files with embedded Walnut expressions

Source files are encoded in UTF-8.

## 1.2 Comments

Walnut supports C-style comments:

```walnut
/* This is a multi-line comment
   spanning multiple lines */

/* This is also a comment */
```

Single-line comments are not supported. Use `/* */` for all comments.

## 1.3 Identifiers

Identifiers are used for variable names, type names, method names, and module names.

**Syntax:**
```
identifier: [a-zA-Z_][a-zA-Z0-9_]*
```

**Examples:**
```walnut
myVariable
MyType
my_function
calculate_total
_privateValue
```

**Type identifiers** must start with an uppercase letter:
```walnut
ProductId
CustomerName
OrderStatus
```

**Function and variable identifiers** must start with a lowercase letter:
```walnut
calculateTotal
userName
getData
```

## 1.4 Keywords

The following are reserved keywords in Walnut:

### Type Keywords
- `Any` - Top type
- `Nothing` - Bottom type
- `Null` - Null atom type
- `Boolean`, `True`, `False` - Boolean types
- `Integer`, `Real` - Numeric types
- `String` - String type
- `Array`, `Map`, `Set` - Collection types
- `Tuple`, `Record` - Structured types
- `Function` - Function type meta-type
- `Mutable` - Mutable value type
- `Type` - Type value type
- `Result`, `Error` - Error handling types
- `ExternalError` - External error type
- `Impure` - Impure computation type (shorthand for `Result<T, ExternalError>`)
- `Shape` - Shape type
- `OptionalKey` - Optional key type (for records)

### Meta-Type Keywords
- `Atom` - Atom meta-type
- `Enumeration`, `EnumerationSubset` - Enumeration meta-types
- `IntegerSubset`, `RealSubset`, `StringSubset` - Subset meta-types
- `Alias` - Alias meta-type
- `Data` - Data meta-type
- `Open` - Open type meta-type
- `Sealed` - Sealed type meta-type
- `Named` - Named type meta-type
- `Union`, `Intersection` - Composite meta-types
- `MutableValue` - Mutable value meta-type

### Value Keywords
- `null` - Null value
- `true`, `false` - Boolean values

### Control Flow Keywords
- `?when` - Conditional if-then-else
- `?whenValueOf` - Value matching
- `?whenTypeOf` - Type matching
- `?whenIsTrue` - Boolean condition matching
- `?whenIsError` - Error checking
- `?noError` - Error early return
- `?noExternalError` - External error early return

### Declaration Keywords
- `module` - Module declaration
- `val` - Constant value marker

### Special Keywords
- `type` - Type value constructor
- `mutable` - Mutable value constructor
- `var` - Multi-variable assignment

### Range Keywords
- `MinusInfinity`, `PlusInfinity` - Infinity bounds

## 1.5 Literals

### 1.5.1 Integer Literals

```walnut
42
0
-17
1000000
```

**Syntax:** Optional `-` sign followed by decimal digits.

### 1.5.2 Real Literals

```walnut
3.14
-2.71
0.5
100.0
```

**Syntax:** Optional `-` sign, decimal digits, `.`, decimal digits.

### 1.5.3 String Literals

String literals are enclosed in single quotes:

```walnut
'Hello, World!'
''
'It\'s a beautiful day'
'Line 1\nLine 2'
'Tab\there'
'Backslash: \\'
```

**Escape sequences:**
- `` \` `` - Single quote
- ` \\ ` - Backslash
- ` \n ` - Newline

### 1.5.4 Boolean Literals

```walnut
true
false
```

### 1.5.5 Null Literal

```walnut
null
```

## 1.6 Operators

### 1.6.1 Arithmetic Operators

```walnut
+   /* Addition / Unary plus */
-   /* Subtraction / Unary minus */
*   /* Multiplication */
/   /* Division */
//  /* Integer division */
%   /* Modulo */
**  /* Power */
```

### 1.6.2 Comparison Operators

```walnut
==  /* Equal */
!=  /* Not equal */
<   /* Less than */
<=  /* Less than or equal */
>   /* Greater than */
>=  /* Greater than or equal */
```

### 1.6.3 Logical Operators

```walnut
&&  /* Logical AND */
||  /* Logical OR */
^^  /* Logical XOR */
```

### 1.6.4 Bitwise Operators

```walnut
&   /* Bitwise AND */
|   /* Bitwise OR */
^   /* Bitwise XOR */
```

### 1.6.5 Type Operators

```walnut
|   /* Union type */
&   /* Intersection type */
\   /* Proxy type */
```

### 1.6.6 Method Call Operators

```walnut
->  /* Method call */
=>  /* Early return / method call with error check */
|>  /* Method call with external error check */
*>  /* Error to external error conversion */
```

### 1.6.7 Other Operators

```walnut
.   /* Property access */
::  /* Scoped expression / function body */
=>  /* Function return type separator / early return */
%   /* Modulo / dependency variable prefix */
#   /* Parameter variable */
$   /* Target variable */
%   /* Dependency variable */
@   /* Error value constructor */
~   /* Default case / destructuring shorthand */
!   /* Data value constructor */
?   /* Optional key prefix */
^   /* Function type marker */
`   /* Type value marker (backtick) */
```

## 1.7 Punctuation

```walnut
(   )   /* Grouping, atom definition, function parameters */
[   ]   /* Tuple, record, array indexing, type refinement */
{   }   /* Sequence block, shape type shorthand */
<   >   /* Type parameters, range constraints */
:   /* Key-value separator, type annotation */
;   /* Set separator, sequence separator */
,   /* List separator */
...     /* Rest type / spread operator */
```

## 1.8 Whitespace

Whitespace (spaces, tabs, newlines) is generally insignificant except:
- To separate tokens
- Inside string literals (preserved)

**Example:**
```walnut
/* These are equivalent: */
x=42;
x = 42;

/* These are equivalent: */
f(x)
f (  x  )
```

## 1.9 Naming Conventions

While not enforced by the language, the following conventions are recommended:

### Types
- **Types**: PascalCase (e.g., `ProductId`, `CustomerOrder`)
- **Type aliases**: PascalCase (e.g., `UserId`)
- **Atoms**: PascalCase (e.g., `NotFound`)
- **Enumerations**: PascalCase (e.g., `OrderStatus`)
- **Enum values**: PascalCase (e.g., `OrderStatus.Pending`)

### Functions and Variables
- **Functions**: camelCase (e.g., `calculateTotal`, `getUserById`)
- **Variables**: camelCase (e.g., `userName`, `totalPrice`)
- **Methods**: camelCase (e.g., `->getName`, `->calculateDiscount`)

### Modules
- **Module names**: kebab-case matching file paths (e.g., `user-service`, `http/router`)

## 1.10 Special Syntax Forms

### 1.10.1 Multi-variable Assignment

```walnut
var{a, b, c} = value         /* Destructure tuple */
var{a: x, b: y} = value      /* Destructure record */
var{~id, ~name} = value      /* Shorthand: id: id, name: name */
```

### 1.10.2 Type Value Syntax

```walnut
`Integer                      /* Type value (short form) */
type[a: String, b: Integer]   /* Record type value */
type[String, Integer]         /* Tuple type value */
```

### 1.10.3 Constant Value Syntax

```walnut
val{42}                       /* Explicit constant */
val[1, 2, 3]                  /* Constant tuple */
val[a: 1, b: 2]               /* Constant record */
```

### 1.10.4 Mutable Value Syntax

```walnut
mutable{Type, value}          /* Mutable value */
Mutable(`Type, value)    /* Long form */
```

### 1.10.5 Error Value Syntax

```walnut
@'error message'              /* Error (short form) */
@ErrorType[field: value]      /* Typed error */
Error('error message')        /* Error (long form) */
```
