# Functions

## Overview

Functions are first-class values in Walnut, meaning they can be assigned to variables, passed as arguments, and returned from other functions. A function is a 4-tuple consisting of:

1. **Parameter type** - the type of the input value
2. **Return type** - the type of the output value
3. **Dependency type** - optional dependencies for dependency injection
4. **Body** - an expression that computes the result

Functions are immutable and referentially transparent by default. They capture variables from their enclosing scope, enabling closures.

## Function Syntax

### Basic Function Form

The complete syntax for a function is:

```walnut
^ParamType => ReturnType :: body
```

**Example:**
```walnut
getLength = ^s: String => Integer :: s->length;
add = ^[a: Integer, b: Integer] => Integer :: #a + #b;
```

### With Dependencies

Functions can declare dependencies that are automatically injected:

```walnut
^ParamType => ReturnType %% DepType :: body
```

**Example:**
```walnut
saveUser = ^user: User => Result<UserId, String> %% ~Database :: {
    database.save(user)
};
```

### Parameter Naming

You can name the parameter for clarity:

```walnut
/* Named parameter */
^p: ParamType => ReturnType :: body

/* Access via name */
square = ^x: Integer => Integer :: x * x;
```

**Type-based shorthand** - name the parameter after its type:

```walnut
/* ^~ParamType is shorthand for ^paramType: ParamType */
getProductTitle = ^~Product => Integer :: product->title;
```

**Any parameter shorthand** - omit the type for `Any`:

```walnut
/* ^p is shorthand for ^p: Any */
printValue = ^v => String :: v->printed;
```

### Null Parameter

When a function takes no input (parameter type is `Null`):

```walnut
/* Long form */
^Null => ReturnType :: body

/* Short form (omit Null) */
^=> ReturnType :: body

/* Example */
getTimestamp = ^=> Integer :: 1234567890;
```

## Function Types

### Type Annotation

Function types describe the signature without the implementation:

```walnut
^ParamType => ReturnType
```

**Examples:**
```walnut
/* Simple function type */
Transformer = ^String => Integer;

/* Complex parameter types */
Processor = ^[x: Integer, y: String] => Boolean;
Validator = ^String => Result<Boolean, ValidationError>;

/* Function with dependencies */
Saver = ^User => *UserId %% ~Database;
```

### First-Class Functions

Functions are values and can be:

1. **Assigned to variables**
   ```walnut
   add = ^[a: Integer, b: Integer] => Integer :: #a + #b;
   ```

2. **Passed as arguments**
   ```walnut
   map = ^[items: Array<Integer>, fn: ^Integer => String] => Array<String> ::
       items->map(fn);
   ```

3. **Returned from functions**
   ```walnut
   makeAdder = ^x: Integer => ^Integer => Integer ::
       ^y: Integer => Integer :: x + y;

   addFive = makeAdder(5);
   result = addFive(10);  /* Returns 15 */
   ```

### Higher-Order Functions

Functions that accept or return other functions:

```walnut
/* Function that accepts a function */
callMe = ^[str: String, myFunc: ^String => Integer] => Integer ::
    #myFunc(#str);

/* Function that returns a function */
myFuncRetFunc = ^v: Integer => ^String => Integer ::
    ^s: String => Integer :: {s->length} + v;

/* Usage */
result = callMe['hello', ^s: String => Integer :: s->length];  /* Returns 5 */
```

### Type Reflection

Function types support reflection:

```walnut
reflect = ^t: Type<Function> => [parameterType: Type, returnType: Type] ::
    [parameterType: t->parameterType, returnType: t->returnType];

myFunc = ^s: String => Integer :: s->length;
info = reflect(myFunc->type);
/* Returns: [parameterType: `String, returnType: `Integer] */
```

## Parameter Access

### The `#` Variable

The parameter value is always accessible via the `#` variable:

```walnut
double = ^Integer => Integer :: # * 2;
getLength = ^String => Integer :: #->length;
```

When a parameter is named, it's also accessible by name:

```walnut
double = ^x: Integer => Integer :: x * 2;  /* Same as # * 2 */
```

### Tuple Element Access

For tuple parameters, access elements by position:

**Note:** Currently, Walnut uses property access syntax for tuple elements rather than numeric indices.

```walnut
/* Tuple parameter */
swap = ^[Integer, String] => [String, Integer] :: [#->item(1), #->item(0)];

/* Named tuple parameter */
distance = ^p: [Real, Real] => Real ::
    ((p->item(0) * p->item(0)) + (p->item(1) * p->item(1)))->squareRoot;
```

### Record Field Access

For record parameters, access fields by name:

```walnut
/* Using # for record fields */
greet = ^[name: String, age: Integer] => String ::
    'Hello ' + #name + ', you are ' + {#age->asString};

/* Named parameter */
greet = ^p: [name: String, age: Integer] => String ::
    'Hello ' + p.name + ', you are ' + {p.age->asString};

/* Direct field access (shorthand) */
greet = ^[name: String, age: Integer] => String ::
    'Hello ' + #name + ', you are ' + {#age->asString};
```

### Rest Values

For tuple or record types with rest types, access remaining values with `#_`:

```walnut
buildTags = ^[category: String, priority: Integer, ...String] => String ::
    'Priority: ' + {#priority->asString} +
    ', category: ' + #category +
    ', tags: ' + #_->values->combineAsString(', ');

/* Call with extra values */
result = buildTags['tech', 5, 'walnut', 'functional', 'types'];
/* Returns: "Priority: 5, category: tech, tags: walnut, functional, types" */
```

## Dependency Access

### The `%` Variable

Dependencies are accessed via the `%` variable:

```walnut
getCurrentTime = ^=> Integer %% ~Clock ::
    %clock->now;
```

### Dependency Fields

For record-based dependencies, access fields directly:

```walnut
/* Record dependency */
saveData = ^data: String => *Null %% [~Database, ~Logger] :: {
    %logger.log('Saving data');
    %database.save(data)
};

/* Named dependency shorthand (~TypeName creates field name from type) */
/* ~Database becomes database: Database */
/* ~Logger becomes logger: Logger */
```

### Dependency Type Syntax

Dependencies can be:

1. **Single type**: `%% TypeName`
2. **Record of types**: `%% [field1: Type1, field2: Type2]`
3. **Shorthand**: `%% ~TypeName` creates a field named after the type (lowercase)
4. **Multiple dependencies**: `%% [~Database, ~Logger, ~Clock]`

## Closures and Variable Capture

Functions capture variables from their enclosing scope, creating closures:

### Basic Closure

```walnut
adder = ^Null => ^Integer => Integer :: {
    sum = mutable{Integer, 0};
    ^Integer => Integer :: {
        sum->SET({sum->value} + #);
        sum->value
    }
};

sum = adder();
0->upTo(9)->map(sum)->printed;
/* Prints: [1, 3, 6, 10, 15, 21, 28, 36, 45, 55] */
```

### Capturing Variables

```walnut
makeMultiplier = ^factor: Integer => ^Integer => Integer ::
    ^x: Integer => Integer :: x * factor;

times10 = makeMultiplier(10);
times10(5);  /* Returns 50 */
```

### Mutable State in Closures

```walnut
counter = ^Null => [increment: ^=> Integer, get: ^=> Integer] :: {
    count = mutable{Integer, 0};
    [
        increment: ^=> Integer :: {
            count->SET({count->value} + 1);
            count->value
        },
        get: ^=> Integer :: count->value
    ]
};

c = counter();
c.increment();  /* Returns 1 */
c.increment();  /* Returns 2 */
c.get();        /* Returns 2 */
```

## Function Invocation

### Basic Call Syntax

```walnut
/* Regular function call */
add = ^[a: Integer, b: Integer] => Integer :: #a + #b;
result = add([1, 2]);  /* Returns 3 */
```

### Null Parameter Call

Functions with null parameters can be called without arguments:

```walnut
getTimestamp = ^=> Integer :: 1234567890;

/* Both forms work */
result1 = getTimestamp();
result2 = getTimestamp(null);
```

### Record Syntax

For record parameters, use the convenient bracket notation:

```walnut
createUser = ^[name: String, age: Integer] => User :: {
    User[name: #name, age: #age]
};

/* Call with record syntax (no parentheses) */
user = createUser[name: 'Alice', age: 30];
```

### Tuple Syntax

For tuple parameters, use bracket notation:

```walnut
swap = ^[Integer, String] => [String, Integer] :: [#->item(1), #->item(0)];

/* Call with tuple syntax */
result = swap[42, 'hello'];  /* Returns: ['hello', 42] */
```

### Tuple to Record Call

As a special convenience, tuples can be passed to record parameters if the count matches:

```walnut
sum = ^[a: Integer, b: Integer] => Integer :: #a + #b;

/* Both work */
result1 = sum[a: 1, b: 2];  /* Record syntax */
result2 = sum[1, 2];        /* Tuple syntax - positional mapping */
```

## Function Composition

### The `+` Operator - Function Composition

Functions can be composed using the `+` operator, which creates a new function that applies the first function and then the second function to the result.

**Syntax:**
```walnut
function1 + function2
```

**Type signature:**
```walnut
^[^A => B, ^B => C] => ^A => C
```

**Semantics:**
- The `+` operator composes two functions left-to-right
- The return type of the first function must match (or be a subtype of) the parameter type of the second function
- The resulting function takes the parameter type of the first function and returns the return type of the second function
- Equivalent to: `^x :: function2(function1(x))`

**Examples:**

```walnut
/* Basic composition */
double = ^Integer => Integer :: # * 2;
toString = ^Integer => String :: #->asString;

/* Compose: first double, then convert to string */
doubleAndStringify = double + toString;

doubleAndStringify(21);                  /* '42' */

/* Multi-step composition */
increment = ^Integer => Integer :: # + 1;
square = ^Integer => Integer :: # * #;
toString = ^Integer => String :: #->asString;

process = increment + square + toString;
process(4);                              /* '25' (4 + 1 = 5, 5 * 5 = 25) */

/* Composing with type transformations */
parseInteger = ^String => Integer :: /* parse string to integer */;
double = ^Integer => Integer :: # * 2;
toString = ^Integer => String :: #->asString;

pipeline = parseInteger + double + toString;
pipeline('21');                          /* '42' */

/* Practical use - data transformation pipeline */
trimSpaces = ^String => String :: #->trim;
toLowerCase = ^String => String :: #->toLowerCase;
capitalize = ^String => String ::
    #->item(0)->toUpperCase + #->slice(1, #->length);

normalizeAndCapitalize = trimSpaces + toLowerCase + capitalize;
normalizeAndCapitalize('  HELLO  ');     /* 'Hello' */
```

**Comparison with manual composition:**

```walnut
/* Using + operator (left-to-right) */
composed = f + g;
result = composed(x);                    /* Equivalent to: g(f(x)) */

/* Manual composition (right-to-left) */
compose = ^[f: ^A => B, g: ^B => C] => ^A => C ::
    ^x: A => C :: g(f(x));

manual = compose[f: f, g: g];
result = manual(x);                      /* Same result */

/* The + operator is more concise and reads naturally left-to-right */
```

**Type checking:**

```walnut
/* Type mismatch is caught at compile time */
toString = ^Integer => String :: #->asString;
double = ^Integer => Integer :: # * 2;

/* Error: String is not a subtype of Integer */
/* invalid = toString + double;  // Compile error */

/* Correct order */
valid = double + toString;               /* OK: Integer => Integer => String */
```

**Chaining multiple compositions:**

```walnut
/* Build complex transformations */
removeSpaces = ^String => String :: #->replace(' ', '');
toLowerCase = ^String => String :: #->toLowerCase;
reverseString = ^String => String :: #->reverse;
addPrefix = ^String => String :: 'processed: ' + #;

transform = removeSpaces + toLowerCase + reverseString + addPrefix;

transform('Hello World');                /* 'processed: dlrowolleh' */
```

**Practical examples:**

```walnut
/* Validation pipeline */
validateEmail = ^String => Result<String, Error> :: ...;
normalizeEmail = ^String => String :: #->toLowerCase->trim;
sendEmail = ^String => Result<Null, Error> :: ...;

emailPipeline = normalizeEmail + validateEmail;

/* Data processing */
parseJSON = ^String => Result<JsonValue, Error> :: ...;
extractField = ^JsonValue => Result<String, Error> :: ...;
validateField = ^String => Result<String, Error> :: ...;

processData = parseJSON + extractField + validateField;
```

## Syntactic Sugar

Walnut provides several shorthands to reduce verbosity:

### Omit Return Type `Any`

When the return type is `Any`, it can be omitted:

```walnut
/* Long form */
process = ^String => Any :: #->length;

/* Short form */
process = ^String :: #->length;
```

### Omit Parameter Type `Null`

When the parameter type is `Null`:

```walnut
/* Long form */
getTimestamp = ^Null => Integer :: 1234567890;

/* Short form (omit Null) */
getTimestamp = ^=> Integer :: 1234567890;
```

### Combine Both

When both parameter is `Null` and return is `Any`:

```walnut
/* Long form */
doSomething = ^Null => Any :: null;

/* Short form */
doSomething = ^ :: null;
```

### Parameter Type Inference

For named parameters of type `Any`:

```walnut
/* Long form */
printValue = ^v: Any => String :: v->printed;

/* Short form (omit : Any) */
printValue = ^v => String :: v->printed;

/* With return type Any omitted too */
printValue = ^v :: v->printed;
```

### Summary Table

| Long Form | Short Form | Description |
|-----------|------------|-------------|
| `^Param => Any` | `^Param` | Omit `=> Any` |
| `^Null => Return` | `^=> Return` | Omit `Null` parameter |
| `^Null => Any` | `^` | Omit both |
| `^p: Any => Return` | `^p => Return` | Omit `: Any` |
| `^p: Any => Any` | `^p` | Omit both types |

## Complete Examples

### Example 1: String Manipulation

```walnut
/* Function with named parameter */
reverse = ^text: String => String :: {
    /* Implementation would reverse the string */
    text  /* Simplified */
};

/* Usage */
reversed = reverse('hello');
```

### Example 2: Array Processing

```walnut
/* Higher-order function */
filter = ^[items: Array, predicate: ^Any => Boolean] => Array :: {
    /* Filter implementation */
    items  /* Simplified */
};

/* Usage */
numbers = [1, 2, 3, 4, 5, 6];
evens = filter[
    items: numbers,
    predicate: ^n: Integer => Boolean :: n % 2 == 0
];
```

### Example 3: Closure with State

```walnut
createToggle = ^initialState: Boolean => [toggle: ^=> Boolean, get: ^=> Boolean] :: {
    state = mutable{Boolean, initialState};
    [
        toggle: ^=> Boolean :: {
            state->SET(!{state->value});
            state->value
        },
        get: ^=> Boolean :: state->value
    ]
};

/* Usage */
toggle = createToggle(false);
toggle.get();     /* Returns: false */
toggle.toggle();  /* Returns: true */
toggle.toggle();  /* Returns: false */
```

### Example 4: Dependency Injection

```walnut
/* Service with dependencies */
UserService := $[
    findUser: ^userId: Integer => *Result<User, String>
];

findUserById = ^id: Integer => *Result<User, String> %% ~UserService :: {
    userService.findUser(id)
};

/* The dependency is automatically injected at runtime */
```

### Example 5: Function Composition

```walnut
compose = ^[f: ^Integer => String, g: ^String => Boolean] => ^Integer => Boolean ::
    ^x: Integer => Boolean :: g(f(x));

/* Usage */
toString = ^n: Integer => String :: n->asString;
isLong = ^s: String => Boolean :: s->length > 2;

checkNumber = compose[f: toString, g: isLong];
checkNumber(100);  /* Returns: true (length of "100" is 3) */
```

### Example 6: Record Parameter with Rest

```walnut
logMessage = ^[level: String, message: String, ...String] => Null %% ~Logger :: {
    tags = #_->values->combineAsString(', ');
    %logger.log(#level + ': ' + #message + ' [' + tags + ']');
    null
};

/* Usage */
logMessage['ERROR', 'Connection failed', 'database', 'timeout', 'retry'];
```

## Method-Style Functions

While this document focuses on standalone functions, note that Walnut also supports method-style functions (covered in detail in [07-methods.md](07-methods.md)):

```walnut
/* Standalone function */
getLength = ^s: String => Integer :: s->length;

/* Method-style function */
String->getLength(^=> Integer) :: $->length;

/* Both can be called */
result1 = getLength('hello');
result2 = 'hello'->getLength();
```

## Best Practices

### 1. Use Explicit Types for Public APIs

```walnut
/* Good - clear signature */
processUser = ^user: User => Result<Response, Error> :: ...

/* Avoid - unclear signature */
processUser = ^user :: ...
```

### 2. Name Parameters for Clarity

```walnut
/* Good */
calculateTax = ^[amount: Real, rate: Real] => Real :: #amount * #rate;

/* Less clear */
calculateTax = ^[Real, Real] => Real :: #->item(0) * #->item(1);
```

### 3. Use Record Parameters for Multiple Inputs

```walnut
/* Good - named fields */
createProduct = ^[name: String, price: Real, stock: Integer] => Product :: ...

/* Avoid - positional tuples for many parameters */
createProduct = ^[String, Real, Integer] => Product :: ...
```

### 4. Leverage Type Inference Where Appropriate

```walnut
/* For simple cases, shorthand is fine */
double = ^Integer :: # * 2;

/* For complex cases, be explicit */
processData = ^input: [data: Array<Record>, config: Config] => Result<Output, Error> :: ...
```

### 5. Use Dependencies for External Services

```walnut
/* Good - explicit dependencies */
saveUser = ^user: User => *UserId %% ~Database :: ...

/* Avoid - hidden dependencies */
saveUser = ^user: User => *UserId :: ...  /* Where does Database come from? */
```

## Summary

Functions in Walnut are:

- **First-class values** that can be passed around like any other value
- **Strongly typed** with explicit parameter and return types
- **Support closures** by capturing variables from their environment
- **Enable dependency injection** through the `%%` syntax
- **Provide flexible syntax** with multiple shorthand forms
- **Support higher-order programming** through function composition and callbacks

The function system is designed to be both powerful and ergonomic, supporting functional programming patterns while maintaining type safety and clarity.
