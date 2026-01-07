# 9. Expressions

## Overview

Everything in Walnut is an expression - there are no statements. Every expression evaluates to a value, making the language consistently functional and composable. Expressions can be as simple as a literal value or as complex as a multi-step computation with error handling and control flow.

This document covers all expression forms in Walnut, from basic values to advanced control flow constructs.

## Expression Categories

Walnut expressions fall into the following categories:

1. **Values** - Literals, constants, and variable references
2. **Variable Assignments** - Binding values to names
3. **Sequence Expressions** - Multiple expressions evaluated in order
4. **Scoped Expressions** - Isolated variable scopes
5. **Method Calls** - Invoking behavior on values
6. **Function Calls** - Applying functions to arguments
7. **Constructor Calls** - Creating instances of user-defined types
8. **Data Value Construction** - Creating data type values with `!`
9. **Property Access** - Accessing fields via dot notation
10. **Operator Expressions** - Binary and unary operators
11. **Conditional Expressions** - Pattern matching and control flow
12. **Early Returns** - Immediately returning from a function
13. **Type Expressions** - Type values and type operations

## Values

Values are the simplest form of expressions. They evaluate to themselves and have no side effects.

### Constants

Walnut supports various literal constants:

```walnut
/* Primitive constants */
42                          /* Integer */
3.14                        /* Real */
'Hello, World!'             /* String */
true                        /* Boolean */
false                       /* Boolean */
null                        /* Null */

/* Type values */
`Real                  /* Long form */
`Integer                    /* Short form (backtick) */
`String['hi', 'hello']      /* Type with subset */

/* Atom values */
MyAtom                      /* Atom type itself */

/* Enumeration values */
Suit.Clubs                  /* Access enumeration value */
MyEnum.Value1               /* Enumeration value */

/* Tuple literals */
[]                          /* Empty tuple */
[1, 2, 3]                   /* Tuple with elements */
[1, -2.61]                  /* Mixed numeric tuple */

/* Record literals */
[:]                         /* Empty record */
[a: 1, b: -2.61]            /* Record with named fields */
[key: 'value', count: 42]   /* Mixed type record */

/* Set literals */
[;]                         /* Empty set */
[1; 2; 3]                   /* Set with elements */

/* Error values */
@'Error message'            /* Error (short form) */
@ErrorType[field: value]    /* Typed error */

/* Function expressions */
^Integer => String :: #->asString    /* Function literal */

/* Mutable values */
mutable{String, 'initial'}  /* Mutable value */
```

### Variables

Variable references evaluate to the current value bound to the variable name:

```walnut
x                           /* Variable reference */
userName                    /* Variable reference */
myFunction                  /* Variable reference (can be a function) */
```

## Variable Assignments

Variable assignments bind values to names. They are expressions that evaluate to the assigned value.

### Simple Assignment

The simplest form assigns a value to a single variable:

```walnut
x = 42;                     /* Assigns 42 to x, evaluates to 42 */
userName = 'Alice';         /* Assigns 'Alice' to userName */
result = calculate(5, 10);  /* Assigns function result to result */
```

**Key points:**
- Assignments are expressions, not statements
- The value of the assignment is the assigned value
- Variables are immutable once bound (in the same scope)

### Multi-Variable Assignment (Destructuring)

Walnut supports destructuring tuples and records into multiple variables using the `var{}` syntax:

#### Tuple Destructuring

```walnut
/* Destructure tuple into multiple variables */
var{a, b, c} = [1, 2, 3];
/* a = 1, b = 2, c = 3 */

var{x, y} = [3.14, 2.71];
/* x = 3.14, y = 2.71 */

var{first, second, third} = ['hello', 42, true];
/* first = 'hello', second = 42, third = true */
```

#### Record Destructuring

```walnut
/* Destructure record with explicit field mapping */
var{a: x, b: y} = [a: 10, b: 20];
/* x = 10, y = 20 */

var{name: userName, age: userAge} = [name: 'Alice', age: 30];
/* userName = 'Alice', userAge = 30 */
```

#### Shorthand Destructuring

When the variable name matches the field name, use the `~` shorthand:

```walnut
/* Shorthand: ~id means id: id */
var{~id, ~name} = [id: 42, name: 'Product'];
/* Equivalent to: var{id: id, name: name} = [id: 42, name: 'Product'] */
/* id = 42, name = 'Product' */

var{~username, ~email, ~active} = user;
/* Extracts username, email, and active fields */
```

**Use cases:**
- Extracting multiple values from function returns
- Pattern matching on structured data
- Simplifying record field access

## Sequence Expressions

Sequence expressions allow multiple expressions to be evaluated in order. They create a new scope and are the fundamental building block for multi-step computations.

### Syntax

```walnut
{
    expression1;
    expression2;
    expression3;
    finalExpression
}
```

### Value Semantics

The value of a sequence is the value of the **last expression** in the sequence, unless an early return is encountered:

```walnut
{
    x = 4;
    y = 3;
    x + y              /* Value of sequence is 7 */
}

{
    a = 10;
    b = 20;
    c = a + b;
    c * 2              /* Value of sequence is 60 */
}
```

### Variable Scope

Variables defined in a sequence are scoped to that sequence:

```walnut
{
    x = 42;            /* x is available here */
    y = x * 2;         /* y = 84 */
    y                  /* Returns 84 */
}
/* x and y are NOT available here */
```

### Early Returns

An early return exits the sequence immediately, bypassing remaining expressions:

```walnut
{
    x = 10;
    ?when(x > 5) { => 'too large' };  /* Early return */
    'normal result'    /* Not reached */
}
/* Returns 'too large' */

{
    value = calculateValue();
    value?;   /* Returns error if value is an error */
    value->process     /* Only executes if no error */
}
```

### Nested Sequences

Sequences can be nested to create hierarchical scopes:

```walnut
{
    x = 10;
    y = {
        inner = x * 2;    /* Can access outer x */
        inner + 5         /* Returns 25 */
    };
    x + y                 /* Returns 35 */
}
```

### Examples

```walnut
/* Multi-step calculation */
calculateDiscount = ^price: Real => Real :: {
    baseDiscount = ?when(price > 100) { 10 } ~ { 5 };
    multiplier = ?when(price > 500) { 1.5 } ~ { 1.0 };
    baseDiscount * multiplier
};

/* Sequence with early returns */
processOrder = ^order: Order => Result<Receipt, OrderError> :: {
    validatedOrder = validateOrder(order)?;
    payment = processPayment(validatedOrder)?;
    generateReceipt(validatedOrder, payment)
};
```

## Scoped Expressions

Scoped expressions create isolated variable scopes and capture early returns. They are similar to immediately-invoked function expressions (IIFEs) in JavaScript.

### Syntax

```walnut
:: expression
```

The `::` operator creates a new scope for the expression that follows.

### Purpose

Scoped expressions serve two primary purposes:

1. **Isolate variable scope** - Variables defined within don't leak out
2. **Capture early returns** - Early returns exit only the scoped expression, not the containing function

### Basic Usage

```walnut
/* Simple scoped expression */
result = :: {
    x = 10;
    y = 20;
    x + y
};
/* result = 30 */
/* x and y are NOT available here */

/* Multiple scoped expressions */
a = :: 42;
b = :: 'hello';
c = :: [1, 2, 3];
```

### Capturing Early Returns

Without scoped expression, an early return exits the entire function:

```walnut
myFunction = ^Null => Integer :: {
    x = 10;
    ?when(x > 5) { => 99 };  /* Returns from myFunction */
    20
};
/* Returns 99 */
```

With scoped expression, the early return only exits the scoped block:

```walnut
myFunction = ^Null => Integer :: {
    x = :: {
        value = 10;
        ?when(value > 5) { => 99 };  /* Returns from scoped expression */
        20
    };
    x + 1           /* Executes even though early return happened */
};
/* Returns 100 (99 + 1) */
```

### Variable Isolation

```walnut
/* Variables in scoped expression don't leak */
result = :: {
    temp = 'temporary';
    temp->length
};
/* result = 9 */
/* temp is NOT available here */

/* Compare with sequence without :: */
{
    temp = 'temporary';
    temp->length
}
/* temp IS available after this sequence in the same scope */
```

### Use Cases

```walnut
/* Complex conditional logic */
value = :: {
    temp = calculateSomething();
    ?when(temp > 100) { => 'high' };
    ?when(temp > 50) { => 'medium' };
    'low'
};

/* Temporary computation */
result = x + :: {
    a = expensiveComputation1();
    b = expensiveComputation2();
    a * b
} + y;

/* Pattern in demo-all.nut */
getAllExpressions = ^Any => Any :: [
    scoped: :: 'scoped',
    /* ... other expressions */
];
```

### Comparison with Functions

Scoped expressions are like immediately-invoked functions but lighter:

```walnut
/* Using function */
result = {^=> Integer :: {
    x = 10;
    x * 2
}}();

/* Using scoped expression (simpler) */
result = :: {
    x = 10;
    x * 2
};
```

## Method Calls

Method calls invoke behavior associated with a type. They use the `->` operator to separate the target value from the method name.

### Basic Syntax

```walnut
target->methodName(argument)
```

**Components:**
- `target` - The value the method is called on
- `->` - Method call operator
- `methodName` - Name of the method
- `argument` - Parameter passed to the method (optional)

### No-Argument Methods

When a method takes no parameter (or `Null` parameter), the argument can be omitted:

```walnut
'Hello'->length             /* No parentheses */
'Hello'->length()           /* With empty parentheses */
'Hello'->length(null)       /* Explicit null (equivalent) */

person->name                /* Get person's name */
user->isActive              /* Check if user is active */
```

### Single-Argument Methods

```walnut
'Hello'->concat(' World')          /* Concatenate strings */
42->binaryPlus(8)                  /* Add numbers (same as 42 + 8) */
[1, 2, 3]->map(^x :: x * 2)        /* Map over array */
'test'->concat('ing')              /* String concatenation */
```

### Record-Argument Methods

For methods that take record parameters, use bracket notation:

```walnut
point->update[x: 10.0, y: 20.0]
person->rename[firstName: 'John', lastName: 'Doe']
product->adjustPrice[amount: 5.0, reason: 'discount']
/* Tuples may also be used as long as there is no rest parameter in the record */
point->update[10.0, 20.0]  /* Equivalent to point->update[x: 10.0, y: 20.0] */
```

### Tuple-Argument Methods

For methods that take tuple parameters, use square brackets:

```walnut
data->process[42, 'info', true]
coordinates->set[100, 200]
```

### Method Chaining

Methods can be chained for fluent APIs:

```walnut
'Hello'->toLowerCase->trim->length
/* Same as: ('Hello'->toLowerCase)->trim->length */

user
    ->validate
    ->save
    ->sendNotification
```

### Desugaring

Method calls are the primary syntax, but they can be understood as applying the target as the first parameter:

```walnut
'Hello'->length
/* Method call on String type */

[1, 2, 3]->sum
/* Method call on Array type */
```

### Property Access via Methods

The `.` property access operator desugars to the `item` method:

```walnut
obj.fieldName
/* Desugars to: */
obj->item('fieldName')

record.id
/* Desugars to: */
record->item('id')

tuple.0
/* Desugars to: */
tuple->item(0)
```

### Early Return Operators

Special method call operators combine invocation with error checking:

#### `?` - No Error Check

Calls the method and immediately returns if the result is any error:

```walnut
obj->method(arg)?
/* Equivalent to: */
(obj->method(arg))?
```

#### `*?` - No External Error Check

Calls the method and immediately returns if the result is an external error:

```walnut
obj->method(arg)*?
/* Equivalent to: */
(obj->method(arg))*?
```

#### `*>` - Error to External Error

Converts any error to an external error:

```walnut
obj *> ('Error context')
/* Desugars to: */
obj->errorAsExternal('Error context')
```

### Examples

```walnut
/* From demo-all.nut */
getAllExpressions = ^Any => Any :: [
    methodCall: 'method call'->length,
    /* ... other expressions */
];

/* Method chaining */
result = data
    ->validate
    =>process          /* Early return on error */
    ->format;

/* Property access */
person.name            /* Access name property */
person.address.city    /* Nested property access */

/* Record parameter */
point->moveTo[x: 100, y: 200]
```

## Function Calls

Function calls apply functions to arguments. Since functions are first-class values, they can be stored in variables and passed around.

### Basic Syntax

```walnut
functionExpression(argument)
```

The function expression is typically a variable name, but can be any expression that evaluates to a function.

### Desugaring to Method Call

Function calls desugar to method calls on the function value:

```walnut
f(arg)
/* Desugars to: */
f->invoke(arg)
```

This means functions are just values with an `invoke` method, making them consistent with the rest of Walnut's design.

### No-Argument Functions

Functions that take `Null` as a parameter can be called without arguments:

```walnut
getTimestamp()              /* Call with empty parentheses */
getTimestamp(null)          /* Explicit null (equivalent) */

/* Both work for: ^Null => Integer :: ... */
```

### Single-Argument Functions

```walnut
calculateTax(100.0)
processUser(user)
double(42)

/* With function in variable */
myFunc = ^Integer => Integer :: # * 2;
result = myFunc(21);        /* result = 42 */
```

### Record-Argument Functions

Functions with record parameters can use bracket notation:

```walnut
createUser[name: 'Alice', age: 30]
calculate[x: 10, y: 20, operation: 'add']

/* Function definition */
createUser = ^[name: String, age: Integer] => User :: ...;
```

### Tuple-Argument Functions

```walnut
swap([42, 'hello']);
swap[42, 'hello']; /* shorthand call for tuple */

/* Function definition */
swap = ^[Integer, String] => [String, Integer] :: [#1, #0];
```

### Higher-Order Functions

Functions that accept or return functions:

```walnut
/* Function that accepts a function */
map = ^[items: Array, fn: ^Any => Any] => Array :: ...;
result = map[[1, 2, 3], ^x :: x * 2];

/* Function that returns a function */
makeAdder = ^x: Integer => ^Integer => Integer ::
    ^y: Integer => Integer :: x + y;

addFive = makeAdder(5);
result = addFive(10);       /* result = 15 */
```

### Examples

```walnut
/* From demo-all.nut */
getAllExpressions = ^Any => Any :: [
    functionCall: functionName('parameter'),
    /* ... other expressions */
];

/* Nested function calls */
result = processResult(
    transform(
        validate(data)
    )
);

/* Function stored in variable */
calculate = ^[a: Integer, b: Integer] => Integer :: #a + #b;
sum = calculate[a: 10, b: 20];    /* sum = 30 */

/* Function as parameter */
numbers->map(^n :: n * 2)
```

## Constructor Calls

Constructor calls create instances of user-defined open or sealed types. They look similar to function calls but have special semantics.

### Syntax

```walnut
TypeName(value)             /* For single-value types */
TypeName[field: value, ...]  /* For record-based types */
```

### Desugaring

Constructor calls desugar to a special `Constructor` mechanism:

```walnut
TypeName(value)
/* Conceptually desugars to: */
Constructor->asTypeName(value)

TypeName[a: 1, b: 2]
/* Conceptually: */
Constructor->asTypeName([a: 1, b: 2])
```

This allows type constructors to be defined as methods with validation and conversion logic.

### Open Type Constructors

```walnut
/* Define open type */
ProductId := #Integer<1..>;

/* Constructor with conversion */
ProductId(String) :: #->asInteger;

/* Usage */
id = ProductId(42);                    /* Direct construction */
id2 = ProductId('123');                /* Via constructor (String to Integer) */
```

### Sealed Type Constructors

```walnut
/* Define sealed type */
Person := $[name: String, age: Integer];

/* No-argument constructor */
ShoppingCart := $[items: Mutable<Map<Item>>];
ShoppingCart() :: [items: mutable{Map<Item>, [:]}];

/* Usage */
cart = ShoppingCart();                 /* No-argument constructor */
person = Person['Alice', 30];          /* Direct construction */
```

### Constructor with Conversion

```walnut
/* Define type */
MyOpen1 := #[a: Integer, b: Integer];

/* Constructor from Real values */
MyOpen1[a: Real, b: Real] :: [a: #a->asInteger, b: #b->asInteger];

/* Usage */
obj = MyOpen1[a: 3.7, b: -2.3];
/* Converts Reals to Integers: MyOpen1[a: 3, b: -2] */
```

### Constructor with Validation

Constructors can return errors when validation fails:

```walnut
/* Define error type */
InvalidInput := ();

/* Constructor with validation */
MyType[input: Integer] @ InvalidInput :: {
    ?when(input < 0) { => @InvalidInput };
    [value: input]
};

/* Usage */
result = MyType[input: 42];       /* OK: MyType[value: 42] */
error = MyType[input: -1];        /* Error: @InvalidInput */
```

### Examples

```walnut
/* From demo-all.nut */
getAllExpressions = ^Any => Any :: [
    constructorCall: TypeName('parameter'),
    /* ... other expressions */
];

/* Enumeration value access (not construction) */
color = ShirtColor.Blue;
size = ShirtSize.XL;

/* Open type construction */
ProductEvent := #[title: String, price: Real];
event = ProductEvent[title: 'Launch', price: 49.99];

/* Sealed type construction */
Product := $[id: Integer, name: String, price: Real];
product = Product[1, 'Laptop', 999.99];
```

## Data Value Construction

Data types use a special `!` operator for construction, distinct from constructors. This is lighter-weight and can be used in constant expressions.

### Syntax

```walnut
DataTypeName!value
```

### Definition and Usage

```walnut
/* Define data type */
ProductId := #Integer<1..>;
MyData := Integer;

/* Create data value with ! */
id = ProductId!42;
data = MyData!100;

/* Access underlying value */
idValue = id->value;        /* 42 */
```

### Difference from Constructors

```walnut
/* Data type - uses ! */
ProductId := #Integer<1..>;
id = ProductId!42;          /* Direct construction with ! */

/* Open type - uses constructor syntax */
ProductEvent := #[title: String, price: Real];
event = ProductEvent[title: 'Sale', price: 29.99];  /* Constructor syntax */
```

### Use in Constants

Data types can be used in constant expressions where constructors cannot:

```walnut
/* Data type in constant */
ProductId := #Integer<1..>;
allConstants = [
    data: ProductId!42      /* OK - can be used in constant */
];

/* Open type cannot be used in constant */
MyOpen := #[a: Integer];
allConstants2 = [
    open: MyOpen[a: 42]     /* Error - not allowed in constant */
];
```

### Examples

```walnut
/* From demo-all.nut */
getAllExpressions = ^Any => Any :: [
    data: MyData!42,
    /* ... other expressions */
];

/* Multiple data types */
UserId := #Integer<1..>;
OrderId := #Integer<1..>;
ProductCode := #String<1..10>;

user = UserId!123;
order = OrderId!456;
code = ProductCode!'ABC123';
```

## Property Access

Property access provides convenient syntax for accessing fields of records and elements of tuples using dot notation.

### Syntax

```walnut
obj.fieldName               /* Record field access */
obj.propertyName            /* Property access via item method */
```

### Desugaring to `item` Method

Property access desugars to a method call:

```walnut
obj.field
/* Desugars to: */
obj->item('field')
```

This means types can implement the `item` method to control property access behavior.

### Record Field Access

```walnut
/* Record literal */
person = [name: 'Alice', age: 30];

/* Property access */
name = person.name;         /* 'Alice' */
age = person.age;           /* 30 */

/* Nested access */
address = [city: 'London', zip: 'SW1A'];
data = [person: person, address: address];
city = data.address.city;   /* 'London' */
```

### Index Access (Tuples and Arrays)

Numeric property access works for tuples and arrays:

```walnut
/* Tuple */
point = [10, 20];
x = point.0;                /* 10 */
y = point.1;                /* 20 */

/* Array */
items = [100, 200, 300];
first = items.0;            /* 100 */
second = items.1;           /* 200 */
```

This desugars to:

```walnut
arr.0
/* Desugars to: */
arr->item(0)

arr.1
/* Desugars to: */
arr->item(1)
```

### Implementing `item` Method

Types can define their own `item` method:

```walnut
Person := $[name: String, age: Integer];

/* Define item method for property access */
Person->item(^String => Result<String|Integer, String>) ::
    ?whenValueOf(#) {
        'name': $name,
        'age': $age,
        ~: @{'Property not found: ' + #}
    };

/* Usage */
person = Person['Alice', 27];
name = person.name;         /* Calls person->item('name'), returns 'Alice' */
age = person.age;           /* Calls person->item('age'), returns 27 */
job = person.job;           /* Error: @'Property not found: job' */
```

### Union Type Property Access

Property access on union types returns union of possible return types:

```walnut
getProperty = ^Person|Map<String> => Result<String|Integer, String|MapItemNotFound> ::
    #name;                 /* Calls ->item('name') on either Person or Map */
```

### Examples

```walnut
/* From demo-all.nut */
getAllExpressions = ^Any => Any :: [
    propertyAccess: [property: 'value'].property,
    /* ... other expressions */
];

/* Chained property access */
order.customer.address.city

/* Mixed with method calls */
user.profile->update[name: 'Bob']
data.items->map(^x :: x.value)
```

## Operator Expressions

Walnut provides a rich set of operators for arithmetic, comparison, logical operations, and more. All operators desugar to method calls, making them fully extensible.

### Arithmetic Operators

Binary arithmetic operators map to methods on the left operand:

| Operator | Method Name      | Example           | Desugars to                |
|----------|------------------|-------------------|----------------------------|
| `+`      | `binaryPlus`     | `a + b`           | `a->binaryPlus(b)`         |
| `-`      | `binaryMinus`    | `a - b`           | `a->binaryMinus(b)`        |
| `*`      | `binaryMultiply` | `a * b`           | `a->binaryMultiply(b)`     |
| `/`      | `binaryDivide`   | `a / b`           | `a->binaryDivide(b)`       |
| `%`      | `binaryModulo`   | `a % b`           | `a->binaryModulo(b)`       |
| `**`     | `binaryPower`    | `a ** b`          | `a->binaryPower(b)`        |

**Examples:**

```walnut
/* Basic arithmetic */
sum = 10 + 5;               /* 15 */
difference = 10 - 5;        /* 5 */
product = 10 * 5;           /* 50 */
quotient = 10 / 5;          /* 2 */
remainder = 10 % 3;         /* 1 */
power = 2 ** 8;             /* 256 */

/* Complex expression */
result = (a + b) * (c - d) / 2;
```

### Comparison Operators

| Operator | Method Name              | Example    | Desugars to                      |
|----------|--------------------------|------------|----------------------------------|
| `==`     | `binaryEqual`            | `a == b`   | `a->binaryEqual(b)`              |
| `!=`     | `binaryNotEqual`         | `a != b`   | `a->binaryNotEqual(b)`           |
| `<`      | `binaryLessThan`         | `a < b`    | `a->binaryLessThan(b)`           |
| `<=`     | `binaryLessThanEqual`    | `a <= b`   | `a->binaryLessThanEqual(b)`      |
| `>`      | `binaryGreaterThan`      | `a > b`    | `a->binaryGreaterThan(b)`        |
| `>=`     | `binaryGreaterThanEqual` | `a >= b`   | `a->binaryGreaterThanEqual(b)`   |

**Examples:**

```walnut
/* Comparisons */
isEqual = x == y;
isNotEqual = x != y;
isLess = x < 10;
isGreater = x > 10;
isInRange = x >= 0 && x <= 100;
```

### Logical Operators

| Operator | Method Name  | Example       | Desugars to           |
|----------|--------------|---------------|-----------------------|
| `&&`     | `binaryAnd`  | `a && b`      | `a->binaryAnd(b)`     |
| `\|\|`   | `binaryOr`   | `a \|\| b`    | `a->binaryOr(b)`      |
| `^^`     | `binaryXor`  | `a ^^ b`      | `a->binaryXor(b)`     |

**Examples:**

```walnut
/* Logical combinations */
isValid = isActive && hasPermission;
shouldProcess = isUrgent || isImportant;
toggle = currentState ^^ newState;
```

### Unary Operators

| Operator | Method Name   | Example  | Desugars to          |
|----------|---------------|----------|----------------------|
| `-`      | `unaryMinus`  | `-a`     | `a->unaryMinus()`    |
| `+`      | `unaryPlus`   | `+a`     | `a->unaryPlus()`     |
| `!`      | `unaryNot`    | `!a`     | `a->unaryNot()`      |

**Examples:**

```walnut
/* Unary operations */
negative = -x;
positive = +x;
inverted = !flag;
```

### Type Operators

| Operator | Purpose              | Example           | Description                    |
|----------|----------------------|-------------------|--------------------------------|
| `\|`     | Union type           | `A\|B`            | Union of types A and B         |
| `&`      | Intersection type    | `A & B`           | Intersection of types A and B  |
| `\`      | Proxy type           | `\TypeName`       | Proxy to TypeName              |

**Examples:**

```walnut
/* Union type */
result: Integer|String = 42;
error: String|NotFound = @NotFound;

/* Intersection type */
combined: Record1 & Record2 = ...;
```

### Custom Operator Overloading

Types can define their own operator behavior:

```walnut
/* Define type */
Temperature := #Real;

/* Override + operator */
Temperature->binaryPlus(^Temperature => Temperature) ::
    Temperature!{$$ + #->value};

/* Usage */
t1 = Temperature!25.0;
t2 = Temperature!10.0;
t3 = t1 + t2;               /* Temperature!35.0 */
```

### Operator Precedence

Operators follow standard mathematical precedence rules (from highest to lowest):

1. **Unary operators**: `-`, `+`, `!`
2. **Power**: `**`
3. **Multiplicative**: `*`, `/`, `%`
4. **Additive**: `+`, `-`
5. **Comparison**: `<`, `<=`, `>`, `>=`
6. **Equality**: `==`, `!=`
7. **Logical AND**: `&&`
8. **Logical XOR**: `^^`
9. **Logical OR**: `||`

Use parentheses to override precedence:

```walnut
result = a + b * c;         /* Same as: a + (b * c) */
result = (a + b) * c;       /* Override precedence */
result = a && b || c;       /* Same as: (a && b) || c */
result = a && (b || c);     /* Override precedence */
```

## Conditional Expressions

Walnut provides several conditional expression forms for pattern matching and control flow. All are covered in detail in [10-conditional-expressions.md](10-conditional-expressions.md).

### Quick Reference

#### `?when` - If-Then-Else

```walnut
?when(condition) { thenBranch } ~ { elseBranch }
?when(condition) { thenBranch }                      /* Else returns null */
```

#### `?whenValueOf` - Value Matching

```walnut
?whenValueOf(expression) {
    value1: result1,
    value2: result2,
    ~: defaultResult
}
```

#### `?whenTypeOf` - Type Matching

```walnut
?whenTypeOf(expression) {
    `Type1: result1,
    `Type2: result2,
    ~: defaultResult
}
```

#### `?whenIsTrue` - Boolean Conditions

```walnut
?whenIsTrue {
    condition1: result1,
    condition2: result2,
    ~: defaultResult
}
```

#### `?whenIsError` - Error Handling

```walnut
?whenIsError(expression) { errorHandler } ~ { successHandler }
?whenIsError(expression) { errorHandler }            /* Else returns expression */
```

### Examples

```walnut
/* From demo-all.nut */
getAllExpressions = ^Any => Any :: [
    matchTrue: ?whenIsTrue { 'then 1': 'then 1', 'then 2': 'then 2', ~: 'default' },
    matchType: ?whenTypeOf ('type') { `String['type']: 'then 1', `String['other type']: 'then 2', ~: 'default' },
    matchValue: ?whenValueOf ('value') { 'value': 'then 1', 'other value': 'then 2', ~: 'default' },
    matchIfThenElse: ?when('condition') { 'then' } ~ { 'else' },
    matchIfThen: ?when('condition') { 'then' },
    matchIsErrorElse: ?whenIsError('condition') { 'then' } ~ { 'else' },
    matchIsError: ?whenIsError('condition') { 'then' }
];
```

## Early Returns

Early returns immediately exit from the current function or scoped expression, returning a value. They are covered in detail in [11-early-returns-errors.md](11-early-returns-errors.md).

### Unconditional Return

```walnut
=> expression
```

Immediately returns the value of the expression:

```walnut
processValue = ^x: Integer => String :: {
    ?when(x < 0) { => 'negative' };     /* Return early */
    ?when(x == 0) { => 'zero' };        /* Return early */
    'positive'                          /* Normal return */
};
```

### Error Returns

#### `?noError` - Return on Any Error

```walnut
?noError(expression)
```

If the expression is an error, return it immediately:

```walnut
process = ^input => Result<Output, Error> :: {
    validated = validate(input);
    ?noError(validated);                /* Return if error */

    transformed = transform(validated);
    ?noError(transformed);              /* Return if error */

    save(transformed)
};
```

#### `?noExternalError` - Return on External Error

```walnut
?noExternalError(expression)
```

If the expression is an `ExternalError`, return it immediately:

```walnut
process = ^input => Impure<Output> :: {
    data = fetchData();
    ?noExternalError(data);             /* Return if external error */

    process(data)
};
```

### Examples

```walnut
/* From demo-all.nut */
getAllExpressions = ^Any => Any :: [
    return: ?when(0) { => 'return' },
    noError: ?noError('no error'),
    noExternalError: ?noExternalError('no external error')
];
```

## Expression Type Inference

Walnut features sophisticated type inference that determines expression types automatically in most contexts.

### Inference Rules

1. **Literals** - Type is determined by the literal form
   ```walnut
   42                          /* Integer */
   3.14                        /* Real */
   'hello'                     /* String */
   true                        /* Boolean (True) */
   [1, 2, 3]                   /* [Integer, Integer, Integer] */
   [a: 1, b: 'x']              /* [a: Integer, b: String] */
   ```

2. **Variables** - Type is the type of the assigned value
   ```walnut
   x = 42;                     /* x: Integer */
   name = 'Alice';             /* name: String */
   values = [1, 2, 3];         /* values: [Integer, Integer, Integer] */
   ```

3. **Function calls** - Type is the return type of the function
   ```walnut
   result = calculate(5);      /* result: ReturnType of calculate */
   ```

4. **Method calls** - Type is the return type of the method
   ```walnut
   length = 'hello'->length;   /* length: Integer */
   ```

5. **Sequences** - Type is the type of the last expression
   ```walnut
   value = {
       x = 10;                 /* x: Integer */
       y = 20;                 /* y: Integer */
       x + y                   /* Type: Integer */
   };                          /* value: Integer */
   ```

6. **Conditionals** - Type is the union of branch types
   ```walnut
   result = ?when(condition) { 42 } ~ { 'error' };
   /* result: Integer|String */
   ```

### Type Annotations

While inference is powerful, you can provide explicit type annotations:

```walnut
/* Variable with type annotation */
x: Integer = 42;
name: String = 'Alice';
values: Array<Integer> = [1, 2, 3];

/* Function with explicit types */
calculate = ^x: Integer => Real :: x * 3.14;

/* Method with explicit types */
Person->age(=> Integer) :: $age;
```

### Type Widening

Walnut automatically widens types when necessary:

```walnut
/* Integer widens to Real in arithmetic */
x: Integer = 10;
y: Real = 3.14;
result = x + y;                 /* result: Real */

/* Specific types widen to union types */
value = ?when(condition) { 42 } ~ { 'text' };
/* value: Integer|String */
```

### Type Narrowing

Type narrowing occurs in conditional branches:

```walnut
process = ^value: Integer|String => String :: {
    ?whenTypeOf(value) {
        `Integer: {
            /* value is narrowed to Integer here */
            value->asString
        },
        `String: {
            /* value is narrowed to String here */
            value
        }
    }
};
```

## Expression Examples Summary

Here's a comprehensive example showing various expression forms:

```walnut
/* From demo-all.nut - complete expression showcase */
getAllExpressions = ^Any => Any :: [
    /* Values */
    constant: 'constant',
    variableName: variableName,

    /* Collections */
    tuple: ['tuple', 1, 2, 3],
    record: [key: 'tuple', a: 1, b: 2, c: 3],
    set: ['set'; 1; 2; 3],

    /* Sequence */
    sequence: {
        'evaluated';
        'evaluated and used'
    },

    /* Scoped expression */
    scoped: :: 'scoped',

    /* Early returns */
    return: ?when(0) { => 'return' },
    noError: ?noError('no error'),
    noExternalError: ?noExternalError('no external error'),

    /* Variable assignment */
    variableAssignment: variableName = 'variable assignment',

    /* Calls */
    methodCall: 'method call'->length,
    functionCall: functionName('parameter'),
    constructorCall: TypeName('parameter'),

    /* Property access */
    propertyAccess: [property: 'value'].property,

    /* Data value */
    data: MyData!42,

    /* Function expression */
    functionBody: ^Any => Any :: 'function body',

    /* Mutable */
    mutable: mutable{String, 'mutable'},

    /* Conditionals */
    matchTrue: ?whenIsTrue { 'then 1': 'then 1', 'then 2': 'then 2', ~: 'default' },
    matchType: ?whenTypeOf ('type') { `String['type']: 'then 1', `String['other type']: 'then 2', ~: 'default' },
    matchValue: ?whenValueOf ('value') { 'value': 'then 1', 'other value': 'then 2', ~: 'default' },
    matchIfThenElse: ?when('condition') { 'then' } ~ { 'else' },
    matchIfThen: ?when('condition') { 'then' },
    matchIsErrorElse: ?whenIsError('condition') { 'then' } ~ { 'else' },
    matchIsError: ?whenIsError('condition') { 'then' }
];
```

## Best Practices

### 1. Use Sequence Expressions for Multi-Step Logic

```walnut
/* Good - clear sequence */
processOrder = ^order: Order => Result<Receipt, Error> :: {
    validated = validateOrder(order);
    ?noError(validated);

    processed = processPayment(validated);
    ?noError(processed);

    generateReceipt(processed)
};
```

### 2. Use Scoped Expressions for Temporary Calculations

```walnut
/* Good - isolate temporary variables */
total = basePrice + :: {
    taxRate = getTaxRate(region);
    basePrice * taxRate
} + shippingCost;
```

### 3. Leverage Destructuring for Complex Returns

```walnut
/* Good - clear destructuring */
var{~success, ~message, ~data} = validateAndProcess(input);
?when(!success) { => @ValidationError[message: message] };
data
```

### 4. Use Property Access for Readable Code

```walnut
/* Good - readable property access */
customerName = order.customer.name;
totalPrice = order.items->map(^item :: item.price)->sum;

/* Avoid - harder to read */
customerName = order->item('customer')->item('name');
```

### 5. Chain Methods for Fluent APIs

```walnut
/* Good - fluent chaining */
result = data
    ->validate
    ->transform
    ->normalize
    ->save;
```

### 6. Use Explicit Types for Public APIs

```walnut
/* Good - clear types */
public processUser = ^user: User => Result<Response, Error> :: ...;

/* Avoid - unclear types */
processUser = ^user :: ...;
```

## Summary

Walnut's expression system provides:

- **Consistency** - Everything is an expression with a value
- **Composability** - Expressions can be freely combined and nested
- **Type safety** - Strong static typing with inference
- **Flexibility** - Multiple forms for different use cases
- **Clarity** - Syntax that clearly expresses intent
- **Power** - Advanced features like pattern matching and error handling

The expression system is designed to make code both concise and explicit, with syntactic sugar for common patterns while maintaining full expressive power for complex scenarios.
