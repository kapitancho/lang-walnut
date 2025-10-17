# Methods

## Overview

Methods in Walnut are type-bound functions that define behavior for specific types. Unlike traditional object-oriented programming where methods are defined within classes, Walnut methods are defined externally and bound to types through a sophisticated dispatch system based on type matching.

Methods are the primary mechanism for extending types with behavior and form the foundation of Walnut's expressive and flexible type system.

## Method Definition Syntax

### Full Form

The complete method definition syntax is:

```walnut
TargetType->methodName(^ParamType => ReturnType) %% DepType :: body
```

**Components:**

- `TargetType`: The type this method is defined for (the receiver)
- `->methodName`: The method name
- `^ParamType => ReturnType`: The function signature
  - `^ParamType`: The parameter type (can be omitted for `Null`)
  - `ReturnType`: The return type (can be omitted for `Any`)
- `%% DepType`: Optional dependency parameter for dependency injection
- `:: body`: The method body

### Syntactic Sugar Variants

Walnut provides extensive syntactic sugar for common method patterns:

#### 1. No Parameter (Null parameter)

```walnut
/* Full form */
Person->age(^Null => Integer) :: $age;

/* Sugar form (parameter omitted) */
Person->age(=> Integer) :: $age;

/* Further sugar (parameter and arrow omitted) */
Person->age() :: $age;
```

#### 2. No Return Type (Any return)

```walnut
/* Full form */
Person->askQuestion(^String => Any) :: ...;

/* Sugar form (return type omitted) */
Person->askQuestion(^String) :: ...;
```

#### 3. Combined: No Parameter, No Return Type

```walnut
/* Implicitly: ^Null => Any */
Person->age() :: $age;
```

#### 4. Named Parameters

```walnut
/* Using parameter name */
Person->greet(^msg: String => String) :: $name + ': ' + msg;

/* Type-named parameter (~Type means param: Type) */
Person->greet(^~Message => String) :: $name + ': ' + message;
```

#### 5. Record and Tuple Parameters

```walnut
/* Record parameter with field extraction */
Person->update(^[name: String, age: Integer] => Person) ::
    Person[#name, #age];

/* Tuple parameter with position extraction */
Point->move(^[Real, Real] => Point) ::
    Point[$x + #.0, $y + #.1];
```

## Target Variable Access

The target of a method call (the value before `->`) is accessible through several special variables:

### `$` - Target Value

The primary way to access the target value:

```walnut
Person := $[name: String, age: Integer];
Person->name(=> String) :: $name;  /* Error: $ is the whole Person, not a record */
Person->name(=> String) :: $name;  /* Correct */
```

### `$$` - Underlying Value (for Open/Sealed types)

For open and sealed types based on another type, `$$` accesses the underlying value:

```walnut
ProductId := #Integer<1..>;
ProductId->value(=> Integer) :: $$;  /* Access the underlying Integer */

Temperature := #Real;
Temperature->celsius(=> Real) :: $$;
```

**Note:** `$$` is only available in methods for open or sealed types.

### `$field` - Auto-Extracted Fields

When the target type is a **sealed type** based on a record or tuple, fields are automatically extracted:

#### Record-Based Sealed Types

```walnut
Person := $[name: String, age: Integer];

/* Fields are automatically extracted */
Person->name(=> String) :: $name;  /* Direct access, not $name */
Person->age(=> Integer) :: $age;
Person->introduce(=> String) :: $name + ' is ' + $age->asString;
```

#### Tuple-Based Sealed Types

```walnut
Point := $[Real, Real];

/* Positions are automatically extracted */
Point->x(=> Real) :: $x;  /* First element */
Point->y(=> Real) :: $y;  /* Second element */
Point->distance(=> Real) :: $x * $x + $y * $y;
```

**Important:** Field extraction only works for **sealed types** (defined with `$[...]`). For regular record/tuple types, use `$.field` or `#.field`.

### `$_` - Rest Values

For tuple/record types with rest types (`...`), `$_` accesses the rest values:

```walnut
FlexibleRecord := $[a: Integer, b: String, ... Real];

FlexibleRecord->firstExtra(=> Result<Real, Any>) ::
    $_->item('c');  /* Access rest values */
```

## Parameter Variable Access

The parameter passed to a method is accessible through several mechanisms:

### `#` - Parameter Value

The primary way to access the parameter:

```walnut
String->concat(^String => String) :: $ + #;
```

### `#field` - Auto-Extracted Fields

Similar to target extraction, parameter fields are automatically extracted for record and tuple types:

#### Record Parameters

```walnut
Person->update(^[name: String, age: Integer] => Person) ::
    Person[#name, #age];  /* Direct access to #name and #age */
```

#### Tuple Parameters

```walnut
Point->move(^[Real, Real] => Point) ::
    Point[$x + #x, $y + #y];  /* #x is first element, #y is second */
```

**Field extraction rules:**

1. **Record types**: Extract named fields
   - `^[a: Integer, b: String]` → `#a`, `#b` available
2. **Tuple types**: Extract by position names
   - `^[Real, Real]` → `#.0`, `#.1` for positional access
   - Named extraction depends on context

### Named Parameters

Explicitly name the parameter for clarity:

```walnut
/* Using parameter name */
Person->greet(^msg: String => String) :: msg + ', ' + $name;

/* Multiple named parameters in record */
Calculator->add(^[x: Integer, y: Integer] => Integer) :: x + y;
```

### Type-Named Parameters (`~Type`)

Use `~Type` as shorthand for `paramName: Type` where `paramName` is the lowercase type name:

```walnut
Message := #String;

/* These are equivalent: */
Person->send(^message: Message => Null) :: ...;
Person->send(^~Message => Null) :: ...;  /* message: Message */

/* Multiple type-named parameters */
BookManager->bookByIsbn(^~Isbn => Result<Book, UnknownBook>) ::
    bookByIsbn(isbn);  /* 'isbn' variable from ~Isbn */
```

## Dependency Variable Access

Methods can declare dependencies for compile-time dependency injection:

### `%` - Dependency Value

Access the injected dependency:

```walnut
Person->greet(=> String) %% CommunicationProtocol ::
    %->prefix + $name;
```

### `%field` - Auto-Extracted Fields

Record-based dependencies have fields automatically extracted:

```walnut
Config := $[host: String, port: Integer];

Server->connect(=> String) %% Config ::
    %host + ':' + %port->asString;  /* Direct access to %host, %port */
```

### Named Dependencies

Use field names in record dependencies or explicit naming:

```walnut
/* Record dependency with field names */
Person->greet(=> String) %% [protocol: CommunicationProtocol] ::
    %protocol->prefix + $name;

/* Type-named dependency */
Person->greet(=> String) %% [~CommunicationProtocol] ::
    %communicationProtocol->prefix + $name;
```

## Method Resolution

Walnut uses **most specific type matching** for method resolution:

### Basic Resolution

When a method is called, Walnut finds the method with the most specific matching target type:

```walnut
Integer->toString(=> String) :: 'Integer: ' + $->asString;
Integer<1..10>->toString(=> String) :: 'Small: ' + $->asString;

x = 5;
x->toString();  /* Calls Integer->toString (x has type Integer[5]) */

y = 5;  /* If explicitly typed as Integer<1..10> in function parameter */
y->toString();  /* Calls Integer<1..10>->toString (more specific) */
```

### Overloading Rules

Methods can be overloaded based on:

1. **Target type specificity**
2. **Parameter type specificity**

```walnut
/* Overloading on target type */
Array<Integer>->sum(=> Integer) :: ...;
Array<Real>->sum(=> Real) :: ...;

/* Overloading on parameter type doesn't work. Only the last method will be used. */
String->concat(^String => String) :: $ + #;
String->concat(^Integer => String) :: $ + #->asString;
```

**Resolution priority:**
1. The latest defined method which matches they target type
2. Compilation error if ambiguous (some cases with union/intersection types)

## Method Invocation

### Basic Invocation

```walnut
obj->method(arg)
```

### Null Parameter (No Argument)

```walnut
/* These are equivalent: */
obj->method(null)
obj->method
/* This is not allowed: */
obj->method()
```

### Record Syntax for Named Arguments

Use record syntax for clarity with record parameters:

```walnut
Point->update(^[x: Real, y: Real] => Point) :: Point[#x, #y];

/* Invocation */
point->update[x: 10.0, y: 20.0]
```

### Method Chaining

Methods can be chained:

```walnut
person->name->toLowerCase->trim
```

## Special Operators

Binary and unary operators are implemented as methods:

### Binary Operators

| Operator | Method Name        | Example              |
|----------|--------------------|----------------------|
| `+`      | `binaryPlus`       | `a + b` = `a->binaryPlus(b)` |
| `-`      | `binaryMinus`      | `a - b` = `a->binaryMinus(b)` |
| `*`      | `binaryMultiply`   | `a * b` = `a->binaryMultiply(b)` |
| `/`      | `binaryDivide`     | `a / b` = `a->binaryDivide(b)` |
| `%`      | `binaryModulo`     | `a % b` = `a->binaryModulo(b)` |
| `^`      | `binaryPower`      | `a ^ b` = `a->binaryPower(b)` |
| `==`     | `binaryEqual`      | `a == b` = `a->binaryEqual(b)` |
| `!=`     | `binaryNotEqual`   | `a != b` = `a->binaryNotEqual(b)` |
| `>`      | `binaryGreaterThan` | `a > b` = `a->binaryGreaterThan(b)` |
| `>=`     | `binaryGreaterThanEqual` | `a >= b` = `a->binaryGreaterThanEqual(b)` |
| `<`      | `binaryLessThan`   | `a < b` = `a->binaryLessThan(b)` |
| `<=`     | `binaryLessThanEqual` | `a <= b` = `a->binaryLessThanEqual(b)` |
| `&&`     | `binaryAnd`        | `a && b` = `a->binaryAnd(b)` |
| `\|\|`   | `binaryOr`         | `a \|\| b` = `a->binaryOr(b)` |

**Example:**

```walnut
Integer->binaryPlus(^Integer => Integer) :: /* built-in */;

/* Usage */
5 + 3  /* Equivalent to: 5->binaryPlus(3) */
```

**Type-aware operators:**

```walnut
/* Custom operator for custom type */
Temperature := #Real;
Temperature->binaryPlus(^Temperature => Temperature) ::
    Temperature!{$$ + #->value};

t1 = Temperature!25.0;
t2 = Temperature!10.0;
t3 = t1 + t2;  /* Returns Temperature!35.0 */
```

### Unary Operators

| Operator | Method Name        | Example          |
|----------|--------------------|------------------|
| `-`      | `unaryMinus`       | `-a` = `a->unaryMinus()` |
| `+`      | `unaryPlus`        | `+a` = `a->unaryPlus()` |
| `!`      | `unaryNot`         | `!a` = `a->unaryNot()` |

**Example:**

```walnut
Integer->unaryMinus(=> Integer) :: /* built-in */;

/* Usage */
x = 5;
y = -x;  /* Equivalent to: x->unaryMinus() */
```

### Property Access

Property access using dot notation maps to the `item` method:

```walnut
obj.field  /* Equivalent to: obj->item('field') */
```

**Example:**

```walnut
Person := $[name: String, age: Integer];
Person->item(^String => Result<String|Integer, String>) ::
    ?whenValueOf(#) is {
        'name': $name,
        'age': $age,
        ~: @{'Property not found: ' + #}
    };

person = Person['Alice', 30];
person.name;  /* Equivalent to: person->item('name') */
```

**For union types:**

```walnut
/* Union type property access returns union of possible return types */
getProperty = ^Person|Map<String> => Result<String|Integer, String|MapItemNotFound> ::
    #name;  /* Calls ->item('name') on either Person or Map */
```

## Complete Examples

### Example 1: Person with Methods (from demo-method.nut)

```walnut
module demo-method:

/* Define a dependency type */
CommunicationProtocol := $[prefix: String];
CommunicationProtocol->prefix(=> String) :: $prefix;
==> CommunicationProtocol :: CommunicationProtocol['Dear '];

/* Main type */
Person := $[name: String<1..>, age: Integer<0..>];

/* Method with dependency, no parameter */
Person->greet(=> String) %% [~CommunicationProtocol] ::
    ['Hello, ', %communicationProtocol->prefix, $name]->combineAsString('');

/* Method with parameter and dependency */
Person->message(^String => String) %% CommunicationProtocol ::
    [%->prefix, $name, ', ', #]->combineAsString('');

/* Method with no parameter and no return types (Null => Any) */
Person->age() :: $age;

/* Method with parameter but no return type (=> Any) */
Person->askQuestion(^String) :: ?whenValueOf(#) is {
    'How old are you?': $age,
    'What is your name?': $name,
    ~: 'I do not understand the question'
};

>>> {
    person = Person['Alice', 27];
    [
        greetAlice: person->greet,
        messageToAlice: person->message('How are you?'),
        ageOfAlice: person->age,
        question1: person->askQuestion('How old are you?'),
        question2: person->askQuestion('What is your name?'),
        question3: person->askQuestion('What is your job?')
    ]->printed
};
```

### Example 2: Property Access (from demo-properties.nut)

```walnut
module demo-properties:

Person := $[name: String<1..>, age: Integer<0..>];
Person->item(^String => Result<String|Integer, String>) ::
    ?whenValueOf(#) is {
        'name': $name,
        'age': $age,
        ~: @{'Property not found: ' + #}
    };

>>> {
    /* Property access for union types */
    getPropertyOfUnionType = ^Person|Map<String> => Result<String|Integer, String|MapItemNotFound> ::
        #name;  /* Union of return types from both ->item methods */

    person = Person['Alice', 27];
    [
        name: person.name,
        age: person.age,
        job: person.job,  /* Returns error */

        union1: getPropertyOfUnionType(person),
        union2: getPropertyOfUnionType([name: 'John'])
    ]->printed
};
```

### Example 3: Record and Tuple Field Extraction

```walnut
/* Record parameter extraction */
testClosedRecord = ^[a: Integer, b: String] => Integer ::
    #a + #b->length;  /* #a and #b auto-extracted */

/* Tuple parameter extraction */
testOpenTuple = ^[Integer, String, ... Real] => Integer ::
    #.0 + #.1->length;  /* #.0 and #.1 for positions */

/* Record-based sealed type with field extraction */
Person := $[name: String, age: Integer];
Person->introduce(=> String) ::
    $name + ' is ' + $age->asString;  /* $name, $age auto-extracted */
```

### Example 4: Type-Named Parameters

```walnut
Isbn := #String;
Book := #[~Isbn, title: String];

/* Type-named parameter: ~Isbn means isbn: Isbn */
BookManager->bookByIsbn(^~Isbn => Result<Book, UnknownBook>) %% ~BookByIsbn ::
    bookByIsbn(isbn);  /* 'isbn' variable available */

/* Type-named in record: ~Book means book: Book */
BookManager->removeBook(^~Book => Result<BookRemoved, UnknownBook>) %% cmd: RemoveBookFromLibrary ::
    cmd(book);  /* 'book' variable available */
```

### Example 5: Underlying Value Access ($$)

```walnut
ProductId := #Integer<1..>;
ProductId->value(=> Integer) :: $$;

Temperature := #Real;
Temperature->celsius(=> Real) :: $$;
Temperature->fahrenheit(=> Real) :: $$ * 1.8 + 32;

/* Usage */
id = ProductId!42;
id->value();  /* Returns 42 */

temp = Temperature!25.0;
temp->celsius();     /* Returns 25.0 */
temp->fahrenheit();  /* Returns 77.0 */
```

## Variable Extraction Summary

### Target Variable (`$`)

| Context | Available Variables | Example |
|---------|-------------------|---------|
| **Sealed Record** | `$field` for each field | `Person := $[name: String]` → `$name` |
| **Sealed Tuple** | `$x`, `$y`, etc. based on position | `Point := $[Real, Real]` → `$x`, `$y` |
| **Open/Sealed with base** | `$$` for underlying value | `ProductId := #Integer` → `$$` |
| **Record with rest** | `$_` for rest values | `Flex := $[a: Integer, ...]` → `$_` |
| **Any type** | `$` for whole value | Any type → `$` |

### Parameter Variable (`#`)

| Context | Available Variables | Example |
|---------|-------------------|---------|
| **Record parameter** | `#field` for each field | `^[a: Integer, b: String]` → `#a`, `#b` |
| **Tuple parameter** | `#.0`, `#.1`, etc. | `^[Integer, String]` → `#.0`, `#.1` |
| **Named parameter** | Parameter name | `^msg: String` → `msg` |
| **Type-named** | Lowercase type name | `^~Message` → `message` |
| **Any type** | `#` for whole value | Any parameter → `#` |

### Dependency Variable (`%`)

| Context | Available Variables | Example |
|---------|-------------------|---------|
| **Record dependency** | `%field` for each field | `%% [host: String, port: Integer]` → `%host`, `%port` |
| **Named dependency** | Field name | `%% [db: Database]` → `%db` |
| **Type-named** | Lowercase type name | `%% [~Database]` → `%database` |
| **Single dependency** | `%` for whole value | `%% Database` → `%` |

## Best Practices

1. **Use auto-extraction for sealed types**: Define sealed types with `$[...]` to get automatic field extraction in methods.

2. **Prefer type-named parameters**: Use `~Type` instead of `param: Type` for cleaner code when parameter name matches type name.

3. **Use dependency injection**: Leverage `%%` for testability and decoupling.

4. **Implement `item` for property access**: Define `->item` methods to enable dot notation access.

5. **Override operators meaningfully**: Only override binary/unary operators when it makes semantic sense for your type.

6. **Keep methods focused**: Each method should do one thing well.

7. **Use most specific types**: Define methods on the most specific type possible for better type safety.

## Summary

Walnut's method system provides:

- **Type-bound behavior**: Methods are defined for specific types
- **Flexible syntax**: Multiple syntactic sugar options for common patterns
- **Auto-extraction**: Automatic field/position extraction for sealed types and record/tuple parameters
- **Special variables**: `$`, `#`, `%`, `$$`, `$_` for accessing different contexts
- **Dependency injection**: Built-in support via `%%` syntax
- **Operator overloading**: Binary and unary operators map to methods
- **Property access**: Dot notation via `->item` method
- **Type-based dispatch**: Most specific type matching for method resolution

This combination makes methods in Walnut both powerful and ergonomic for building type-safe, expressive programs.
