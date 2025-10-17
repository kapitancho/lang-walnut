# 21. Hydration

## Overview

Hydration is the process of converting runtime values (typically from external sources like JSON) into strongly-typed Walnut values. Unlike constructors which are explicitly invoked in code, hydration happens automatically when converting untyped or loosely-typed data into Walnut's type system.

## 21.1 Hydration vs Constructors

### 21.1.1 Key Differences

**Constructors:**
- Explicitly invoked in code: `User([id: 1, name: 'Alice'])`
- Used for creating values with validation
- Part of the type definition
- Type signature: `^ParameterType => Result<TargetType, ErrorType>`

**Hydration:**
- Implicitly invoked via `->hydrateAs(targetType)`
- Used for converting external/runtime data
- Works with any type (built-in or user-defined)
- Checks type compatibility and runs validators

**Example:**
```walnut
/* Constructor - explicit */
User := #[id: Integer<1..>, name: String];
User(data) @ String :: /* validation logic */;

user1 = User([id: 1, name: 'Alice']);  /* Constructor */

/* Hydration - automatic conversion */
jsonData = '{"id": 1, "name": "Alice"}';
user2 = jsonData->parseJson->hydrateAs(`User);  /* Hydration */
```

## 21.2 The hydrateAs Method

### 21.2.1 Basic Usage

The `hydrateAs` method is available on `JsonValue` and converts runtime values to typed values.

**Syntax:** `value->hydrateAs(targetType)`

**Parameters:**
- `value` - A `JsonValue` (from JSON parsing or other runtime sources)
- `targetType` - A `Type` value representing the target type

**Returns:** `Result<TargetType, HydrationError>`

**Example:**
```walnut
/* Parse JSON and hydrate */
jsonString = '{"x": 10, "y": 20}';
result = jsonString->parseJson->hydrateAs(`[x: Integer, y: Integer]);

?whenTypeOf(result) is {
    `[x: Integer, y: Integer]: result->printed,
    `HydrationError: result.message->OUT_TXT
};
```

### 21.2.2 HydrationError Structure

When hydration fails, a `HydrationError` is returned with detailed information.

**Structure:**
```walnut
HydrationError := $[
    message: String,        /* Human-readable error message */
    path: Array<String>,    /* Path to the failing field */
    expectedType: String,   /* Expected type name */
    actualValue: JsonValue  /* The value that failed */
];
```

**Example:**
```walnut
/* Invalid data */
jsonString = '{"age": "not a number"}';
result = jsonString->parseJson->hydrateAs(`[age: Integer]);

?whenTypeOf(result) is {
    `HydrationError: {
        result.message->OUT_TXT;
        /* Output: "Expected Integer at path 'age', got String" */
    }
};
```

## 21.3 Hydration by Type Category

### 21.3.1 Primitive Types

#### Null

Hydrates only from JSON null values.

**Examples:**
```walnut
nullValue = 'null'->parseJson->hydrateAs(`Null);
/* Success: Null */

notNull = '5'->parseJson->hydrateAs(`Null);
/* Error: Expected Null, got Integer */
```

#### Boolean

Hydrates from JSON boolean values.

**Examples:**
```walnut
trueValue = 'true'->parseJson->hydrateAs(`Boolean);
/* Success: True */

falseValue = 'false'->parseJson->hydrateAs(`Boolean);
/* Success: False */

notBoolean = '"yes"'->parseJson->hydrateAs(`Boolean);
/* Error: Expected Boolean, got String */
```

#### True and False

Hydrates only from specific boolean values.

**Examples:**
```walnut
trueOnly = 'true'->parseJson->hydrateAs(`True);
/* Success: True */

falseOnly = 'false'->parseJson->hydrateAs(`False);
/* Success: False */

wrongBoolean = 'false'->parseJson->hydrateAs(`True);
/* Error: Expected True, got False */
```

#### Integer

Hydrates from JSON integer values.

**Examples:**
```walnut
intValue = '42'->parseJson->hydrateAs(`Integer);
/* Success: 42 */

notInt = '3.14'->parseJson->hydrateAs(`Integer);
/* Error: Expected Integer, got Real */

notInt2 = '"42"'->parseJson->hydrateAs(`Integer);
/* Error: Expected Integer, got String */
```

#### Real

Hydrates from JSON number values (both integers and reals).

**Examples:**
```walnut
realValue = '3.14'->parseJson->hydrateAs(`Real);
/* Success: 3.14 */

intAsReal = '42'->parseJson->hydrateAs(`Real);
/* Success: 42.0 (integers can be hydrated as Real) */

notReal = '"3.14"'->parseJson->hydrateAs(`Real);
/* Error: Expected Real, got String */
```

#### String

Hydrates from JSON string values.

**Examples:**
```walnut
strValue = '"hello"'->parseJson->hydrateAs(`String);
/* Success: "hello" */

notString = '42'->parseJson->hydrateAs(`String);
/* Error: Expected String, got Integer */
```

### 21.3.2 Refined Primitive Types

#### Integer Ranges

Hydrates integers and validates against range constraints.

**Examples:**
```walnut
Age := Integer<0..150>;

validAge = '25'->parseJson->hydrateAs(`Age);
/* Success: 25 */

tooOld = '200'->parseJson->hydrateAs(`Age);
/* Error: Value 200 outside range 0..150 */

negative = '-5'->parseJson->hydrateAs(`Integer<1..>);
/* Error: Value -5 outside range 1.. */
```

#### Integer Subsets

Hydrates integers and validates against allowed values.

**Examples:**
```walnut
DiceRoll := Integer[1, 2, 3, 4, 5, 6];

validRoll = '4'->parseJson->hydrateAs(`DiceRoll);
/* Success: 4 */

invalidRoll = '7'->parseJson->hydrateAs(`DiceRoll);
/* Error: Value 7 not in [1, 2, 3, 4, 5, 6] */
```

#### Real Ranges

Hydrates real numbers and validates against range constraints.

**Examples:**
```walnut
Probability := Real<0..1>;

validProb = '0.75'->parseJson->hydrateAs(`Probability);
/* Success: 0.75 */

invalidProb = '1.5'->parseJson->hydrateAs(`Probability);
/* Error: Value 1.5 outside range 0..1 */
```

#### String Length Constraints

Hydrates strings and validates length.

**Examples:**
```walnut
Username := String<3..20>;

validUsername = '"alice"'->parseJson->hydrateAs(`Username);
/* Success: "alice" */

tooShort = '"ab"'->parseJson->hydrateAs(`Username);
/* Error: String length 2 outside range 3..20 */

tooLong = '"verylongusernamethatexceedslimit"'->parseJson->hydrateAs(`Username);
/* Error: String length 33 outside range 3..20 */
```

#### String Subsets

Hydrates strings and validates against allowed values.

**Examples:**
```walnut
Status := String['pending', 'active', 'completed'];

validStatus = '"active"'->parseJson->hydrateAs(`Status);
/* Success: "active" */

invalidStatus = '"unknown"'->parseJson->hydrateAs(`Status);
/* Error: Value "unknown" not in ['pending', 'active', 'completed'] */
```

### 21.3.3 Collection Types

#### Arrays

Hydrates JSON arrays and validates element types and length.

**Basic arrays:**
```walnut
intArray = '[1, 2, 3]'->parseJson->hydrateAs(`Array<Integer>);
/* Success: [1, 2, 3] */

mixedArray = '[1, "two", 3]'->parseJson->hydrateAs(`Array<Integer>);
/* Error: Element at index 1: Expected Integer, got String */
```

**Arrays with length constraints:**
```walnut
SmallArray := Array<Integer, 1..5>;

valid = '[1, 2, 3]'->parseJson->hydrateAs(`SmallArray);
/* Success: [1, 2, 3] */

tooLong = '[1, 2, 3, 4, 5, 6]'->parseJson->hydrateAs(`SmallArray);
/* Error: Array length 6 outside range 1..5 */

empty = '[]'->parseJson->hydrateAs(`SmallArray);
/* Error: Array length 0 outside range 1..5 */
```

**Arrays with refined element types:**
```walnut
Ages := Array<Integer<0..150>>;

validAges = '[25, 30, 45]'->parseJson->hydrateAs(`Ages);
/* Success: [25, 30, 45] */

invalidAges = '[25, 200, 45]'->parseJson->hydrateAs(`Ages);
/* Error: Element at index 1: Value 200 outside range 0..150 */
```

#### Maps

Hydrates JSON objects as maps and validates value types.

**Basic maps:**
```walnut
scores = '{"alice": 95, "bob": 87}'->parseJson->hydrateAs(`Map<Integer>);
/* Success: {"alice": 95, "bob": 87} */

invalidMap = '{"alice": 95, "bob": "87"}'->parseJson->hydrateAs(`Map<Integer>);
/* Error: Value at key "bob": Expected Integer, got String */
```

**Maps with length constraints:**
```walnut
SmallMap := Map<String, 1..3>;

valid = '{"a": "x", "b": "y"}'->parseJson->hydrateAs(`SmallMap);
/* Success: {"a": "x", "b": "y"} */

tooLarge = '{"a":"x","b":"y","c":"z","d":"w"}'->parseJson->hydrateAs(`SmallMap);
/* Error: Map length 4 outside range 1..3 */
```

#### Sets

Hydrates JSON arrays as sets (removing duplicates) and validates element types.

**Basic sets:**
```walnut
tags = '["js", "php", "js"]'->parseJson->hydrateAs(`Set<String>);
/* Success: {"js", "php"} (duplicate removed) */
```

**Sets with length constraints:**
```walnut
Tags := Set<String, 1..5>;

valid = '["js", "php", "rust"]'->parseJson->hydrateAs(`Tags);
/* Success: {"js", "php", "rust"} */

tooMany = '["a","b","c","d","e","f"]'->parseJson->hydrateAs(`Tags);
/* Error: Set length 6 outside range 1..5 */
```

### 21.3.4 Structured Types

#### Tuples

Hydrates JSON arrays as tuples with fixed element types.

**Basic tuples:**
```walnut
Point := [Integer, Integer];

validPoint = '[10, 20]'->parseJson->hydrateAs(`Point);
/* Success: [10, 20] */

wrongType = '[10, "20"]'->parseJson->hydrateAs(`Point);
/* Error: Element at index 1: Expected Integer, got String */

wrongLength = '[10, 20, 30]'->parseJson->hydrateAs(`Point);
/* Error: Expected tuple of length 2, got 3 */
```

**Tuples with rest types:**
```walnut
Coords := [Integer, Integer, ...Integer];

valid1 = '[10, 20]'->parseJson->hydrateAs(`Coords);
/* Success: [10, 20] */

valid2 = '[10, 20, 30, 40]'->parseJson->hydrateAs(`Coords);
/* Success: [10, 20, 30, 40] */

invalid = '[10]'->parseJson->hydrateAs(`Coords);
/* Error: Expected at least 2 elements, got 1 */
```

#### Records

Hydrates JSON objects as records with typed fields.

**Basic records:**
```walnut
User := [id: Integer, name: String];

validUser = '{"id": 1, "name": "Alice"}'->parseJson->hydrateAs(`User);
/* Success: [id: 1, name: "Alice"] */

missingField = '{"id": 1}'->parseJson->hydrateAs(`User);
/* Error: Required field "name" is missing */

wrongType = '{"id": "1", "name": "Alice"}'->parseJson->hydrateAs(`User);
/* Error: Field "id": Expected Integer, got String */
```

**Records with optional fields:**
```walnut
Product := [id: Integer, name: String, description?: String];

withOptional = '{"id": 1, "name": "Widget", "description": "A thing"}'
    ->parseJson->hydrateAs(`Product);
/* Success: [id: 1, name: "Widget", description: "A thing"] */

withoutOptional = '{"id": 1, "name": "Widget"}'->parseJson->hydrateAs(`Product);
/* Success: [id: 1, name: "Widget"] */
```

**Records with rest types:**
```walnut
Config := [debug: Boolean, ...String];

valid = '{"debug": true, "host": "localhost", "port": "3000"}'
    ->parseJson->hydrateAs(`Config);
/* Success: [debug: true, "host": "localhost", "port": "3000"] */
```

**Sealed records:**
```walnut
StrictUser := $[id: Integer, name: String];

valid = '{"id": 1, "name": "Alice"}'->parseJson->hydrateAs(`StrictUser);
/* Success: [id: 1, name: "Alice"] */

extraField = '{"id": 1, "name": "Alice", "age": 30}'->parseJson->hydrateAs(`StrictUser);
/* Error: Unexpected field "age" in sealed record */
```

### 21.3.5 User-Defined Types

#### Atoms

Atoms hydrate from any value (they are opaque types).

**Example:**
```walnut
UserId := +Integer;

userId = '42'->parseJson->hydrateAs(`UserId);
/* Success: UserId(42) */
```

#### Enumerations

Hydrates from string values matching enumeration value names.

**Basic enumerations:**
```walnut
Suit := (Spade, Heart, Diamond, Club);

validSuit = '"Spade"'->parseJson->hydrateAs(`Suit);
/* Success: Suit.Spade */

invalidSuit = '"Invalid"'->parseJson->hydrateAs(`Suit);
/* Error: "Invalid" is not a valid value for Suit */
```

**Enumerations with JsonValue cast:**
```walnut
Status := (Pending, Active, Completed);

/* Define custom cast from JSON */
Status.JsonValue = ^JsonValue => Result<Status, HydrationError> :: {
    ?whenTypeOf($) is {
        `JsonValueString: {
            map = [
                'P': Status.Pending,
                'A': Status.Active,
                'C': Status.Completed
            ];
            ?when(map->hasKey($.value)) {
                map->item($.value)
            } ~ {
                @HydrationError([
                    message: 'Invalid status code: ' + $.value,
                    path: [],
                    expectedType: 'Status',
                    actualValue: $
                ])
            }
        },
        ~: @HydrationError([
            message: 'Expected string',
            path: [],
            expectedType: 'Status',
            actualValue: $
        ])
    }
};

status = '"A"'->parseJson->hydrateAs(`Status);
/* Success: Status.Active (via custom cast) */
```

#### Enumeration Subsets

Hydrates from string values, validates against subset.

**Example:**
```walnut
Suit := (Spade, Heart, Diamond, Club);
RedSuit := Suit[Heart, Diamond];

validRed = '"Heart"'->parseJson->hydrateAs(`RedSuit);
/* Success: Suit.Heart */

notRed = '"Spade"'->parseJson->hydrateAs(`RedSuit);
/* Error: "Spade" is not a valid value for RedSuit */
```

#### Data Types

Hydrates records and wraps them in the data type.

**Example:**
```walnut
Point := #[x: Integer, y: Integer];

point = '{"x": 10, "y": 20}'->parseJson->hydrateAs(`Point);
/* Success: Point([x: 10, y: 20]) */

invalid = '{"x": "10", "y": 20}'->parseJson->hydrateAs(`Point);
/* Error: Field "x": Expected Integer, got String */
```

#### Open Types

Hydrates values and runs validator.

**Example:**
```walnut
PositiveInt := #Integer<1..>;

/* Validator */
PositiveInt(value: Integer) @ String :: {
    ?when(value > 0) {
        value
    } ~ {
        @'Value must be positive'
    }
};

valid = '42'->parseJson->hydrateAs(`PositiveInt);
/* Success: 42 */

invalid = '-5'->parseJson->hydrateAs(`PositiveInt);
/* Error: Value must be positive */
```

#### Sealed Types

Hydrates sealed records and runs validator.

**Example:**
```walnut
User := $[id: Integer<1..>, name: String<1..>];

/* Validator */
User(value: [id: Integer<1..>, name: String<1..>]) @ String :: {
    ?when(value.name->length >= 3) {
        value
    } ~ {
        @'Name must be at least 3 characters'
    }
};

valid = '{"id": 1, "name": "Alice"}'->parseJson->hydrateAs(`User);
/* Success: [id: 1, name: "Alice"] */

tooShort = '{"id": 1, "name": "Al"}'->parseJson->hydrateAs(`User);
/* Error: Name must be at least 3 characters */
```

#### Aliases

Hydrates the underlying type.

**Example:**
```walnut
UserId := Integer<1..>;
Age := Integer<0..150>;

userId = '42'->parseJson->hydrateAs(`UserId);
/* Success: 42 */

age = '25'->parseJson->hydrateAs(`Age);
/* Success: 25 */
```

### 21.3.6 Union Types

Union types try each alternative in order and return the first successful hydration.

**Examples:**
```walnut
IntOrString := Integer|String;

asInt = '42'->parseJson->hydrateAs(`IntOrString);
/* Success: 42 (Integer) */

asString = '"hello"'->parseJson->hydrateAs(`IntOrString);
/* Success: "hello" (String) */

neither = 'true'->parseJson->hydrateAs(`IntOrString);
/* Error: Cannot hydrate Boolean as Integer|String */
```

**Optional values:**
```walnut
OptionalInt := Integer|Null;

withValue = '42'->parseJson->hydrateAs(`OptionalInt);
/* Success: 42 */

withNull = 'null'->parseJson->hydrateAs(`OptionalInt);
/* Success: Null */
```

**Complex unions:**
```walnut
Response := [status: 'success', data: String]
          | [status: 'error', message: String];

success = '{"status": "success", "data": "OK"}'->parseJson->hydrateAs(`Response);
/* Success: [status: 'success', data: "OK"] */

error = '{"status": "error", "message": "Failed"}'->parseJson->hydrateAs(`Response);
/* Success: [status: 'error', message: "Failed"] */
```

### 21.3.7 Result Types

Result types handle success and error cases during hydration.

**Examples:**
```walnut
UserResult := Result<User, String>;

/* For Result types, hydration tries the success type first */
validUser = '{"id": 1, "name": "Alice"}'->parseJson->hydrateAs(`UserResult);
/* Success: [id: 1, name: "Alice"] (success case) */

errorMsg = '"User not found"'->parseJson->hydrateAs(`UserResult);
/* Success: "User not found" (error case) */
```

### 21.3.8 Mutable Types

Mutable types hydrate the underlying value and wrap it in a mutable container.

**Example:**
```walnut
counter = '0'->parseJson->hydrateAs(`Mutable<Integer>);
/* Success: Mutable container with value 0 */

counter := 10;  /* Can mutate the value */
```

### 21.3.9 Type Values

The special type `Type` can hydrate from string type names.

**Example:**
```walnut
typeName = '"Integer"'->parseJson->hydrateAs(`Type);
/* Success: Type value representing Integer */

customType = '"MyCustomType"'->parseJson->hydrateAs(`Type);
/* Success: Type value representing MyCustomType (if it exists) */
```

### 21.3.10 Any and Nothing

**Any:**
```walnut
anyValue = '42'->parseJson->hydrateAs(`Any);
/* Success: 42 (any value accepted) */
```

**Nothing:**
```walnut
nothingValue = '42'->parseJson->hydrateAs(`Nothing);
/* Error: Nothing type cannot be hydrated (no values exist) */
```

## 21.4 JsonValue Cast Method

### 21.4.1 Custom Hydration Logic

Types can define a special `JsonValue` cast method to customize hydration behavior.

**Syntax:**
```walnut
TypeName.JsonValue = ^JsonValue => Result<TypeName, HydrationError> :: {
    /* Custom hydration logic */
};
```

**Example:**
```walnut
Temperature := #Real;

/* Custom hydration: convert Fahrenheit to Celsius */
Temperature.JsonValue = ^JsonValue => Result<Temperature, HydrationError> :: {
    ?whenTypeOf($) is {
        `JsonValueNumber: {
            celsius = ($.value - 32) * 5 / 9;
            Temperature(celsius)
        },
        `JsonValueObject: {
            ?when($->hasKey('celsius')) {
                Temperature($->item('celsius'))
            } ~ ?when($->hasKey('fahrenheit')) {
                fahrenheit = $->item('fahrenheit');
                celsius = (fahrenheit - 32) * 5 / 9;
                Temperature(celsius)
            } ~ {
                @HydrationError([
                    message: 'Expected "celsius" or "fahrenheit" field',
                    path: [],
                    expectedType: 'Temperature',
                    actualValue: $
                ])
            }
        },
        ~: @HydrationError([
            message: 'Expected number or object',
            path: [],
            expectedType: 'Temperature',
            actualValue: $
        ])
    }
};

temp1 = '68'->parseJson->hydrateAs(`Temperature);
/* Success: Temperature(20) - converts from Fahrenheit */

temp2 = '{"celsius": 20}'->parseJson->hydrateAs(`Temperature);
/* Success: Temperature(20) - direct Celsius */

temp3 = '{"fahrenheit": 68}'->parseJson->hydrateAs(`Temperature);
/* Success: Temperature(20) - converts from Fahrenheit */
```

### 21.4.2 JsonValue Cast Priority

When hydrating a user-defined type, the system checks for a `JsonValue` cast method first:

1. If `TypeName.JsonValue` exists, use it for hydration
2. Otherwise, use default hydration based on type structure

**Example:**
```walnut
UserId := +Integer;

/* Without JsonValue cast: hydrates from integer */
userId1 = '42'->parseJson->hydrateAs(`UserId);
/* Success: UserId(42) */

/* With JsonValue cast: custom logic */
UserId.JsonValue = ^JsonValue => Result<UserId, HydrationError> :: {
    ?whenTypeOf($) is {
        `JsonValueString: UserId($.value->parseInt),
        `JsonValueNumber: UserId($.value),
        ~: @HydrationError([
            message: 'Expected string or number',
            path: [],
            expectedType: 'UserId',
            actualValue: $
        ])
    }
};

userId2 = '"42"'->parseJson->hydrateAs(`UserId);
/* Success: UserId(42) - now accepts strings too */
```

## 21.5 Validator Integration

### 21.5.1 Validators During Hydration

When hydrating user-defined types with validators (open and sealed types), the validator is automatically executed after structural hydration succeeds.

**Process:**
1. Structurally hydrate the value (check types, ranges, etc.)
2. If successful, pass the value to the validator
3. If validator succeeds, return the value
4. If validator fails, return hydration error

**Example:**
```walnut
Email := #String;

/* Validator checks email format */
Email(value: String) @ String :: {
    ?when(value->contains('@') && value->contains('.')) {
        value
    } ~ {
        @'Invalid email format'
    }
};

validEmail = '"alice@example.com"'->parseJson->hydrateAs(`Email);
/* Success: "alice@example.com" */

invalidEmail = '"not-an-email"'->parseJson->hydrateAs(`Email);
/* Error: Invalid email format */

wrongType = '42'->parseJson->hydrateAs(`Email);
/* Error: Expected String, got Integer (fails before validator) */
```

### 21.5.2 Complex Validation

Validators can perform complex validation logic.

**Example:**
```walnut
Password := #String<8..>;

Password(value: String<8..>) @ String :: {
    hasUpper = value->matches('[A-Z]');
    hasLower = value->matches('[a-z]');
    hasDigit = value->matches('[0-9]');

    ?when(hasUpper && hasLower && hasDigit) {
        value
    } ~ {
        @'Password must contain uppercase, lowercase, and digit'
    }
};

validPassword = '"SecurePass123"'->parseJson->hydrateAs(`Password);
/* Success: "SecurePass123" */

weakPassword = '"password"'->parseJson->hydrateAs(`Password);
/* Error: Password must contain uppercase, lowercase, and digit */

tooShort = '"Pass1"'->parseJson->hydrateAs(`Password);
/* Error: String length 5 outside range 8.. (fails before validator) */
```

## 21.6 Error Handling

### 21.6.1 Error Messages

Hydration errors include detailed information about what went wrong and where.

**Error message format:**
```
Expected <expected-type> at path '<path>', got <actual-type>
```

**Example:**
```walnut
User := [id: Integer, profile: [name: String, age: Integer]];

invalidData = '{"id": 1, "profile": {"name": "Alice", "age": "thirty"}}'
    ->parseJson->hydrateAs(`User);

/* Error message: "Expected Integer at path 'profile.age', got String" */
```

### 21.6.2 Error Paths

The `path` field in `HydrationError` shows the location of the error using dot notation.

**Examples:**
- `""` - Error at root level
- `"name"` - Error in field `name`
- `"profile.age"` - Error in nested field `profile.age`
- `"items[2]"` - Error in array element at index 2
- `"users[0].email"` - Error in nested structure

**Example:**
```walnut
data = '[{"id": 1, "name": "Alice"}, {"id": "two", "name": "Bob"}]'
    ->parseJson->hydrateAs(`Array<[id: Integer, name: String]>);

/* Error path: "[1].id" */
/* Error message: "Expected Integer at path '[1].id', got String" */
```

### 21.6.3 Handling Hydration Errors

**Pattern matching on Result:**
```walnut
result = jsonData->parseJson->hydrateAs(`User);

?whenTypeOf(result) is {
    `User: {
        /* Success case */
        result.name->OUT_TXT
    },
    `HydrationError: {
        /* Error case */
        'Hydration failed: '->OUT_TXT;
        result.message->OUT_TXT;
        '\nPath: '->OUT_TXT;
        result.path->joined('.')
    }
};
```

**Using unwrap or default:**
```walnut
/* Unwrap or provide default */
user = ?whenIsError(jsonData->parseJson->hydrateAs(`User)) { [id: 0, name: 'Unknown'] };

/* Transform error */
result = jsonData->parseJson->hydrateAs(`User)
    @ error :: [message: error.message, timestamp: now()];
```

## 21.7 Common Patterns

### 21.7.1 API Response Parsing

```walnut
ApiResponse := [
    status: Integer<200..299>,
    data: [
        users: Array<[id: Integer<1..>, name: String<1..>, email: String]>
    ]
];

response = httpClient->get('/api/users');
parsed = response.body->parseJson->hydrateAs(`ApiResponse);

?whenTypeOf(parsed) is {
    `ApiResponse: {
        parsed.data.users->forEach(^user :: {
            user.name->OUT_TXT;
            ' <'->OUT_TXT;
            user.email->OUT_TXT;
            '>'->OUT_LN
        })
    },
    `HydrationError: {
        'API response validation failed: '->OUT_TXT;
        parsed.message->OUT_TXT
    }
};
```

### 21.7.2 Configuration Loading

```walnut
Config := $[
    database: [
        host: String<1..>,
        port: Integer<1..65535>,
        name: String<1..>
    ],
    logging: [
        level: String['debug', 'info', 'warning', 'error'],
        file?: String
    ]
];

loadConfig = ^path: String => Result<Config, String> :: {
    content = path->readFile;
    parsed = content->parseJson;
    hydrated = parsed->hydrateAs(`Config);

    hydrated @ error :: 'Config validation failed: ' + error.message
};

config = loadConfig('config.json');
?whenTypeOf(config) is {
    `Config: {
        'Connected to: '->OUT_TXT;
        config.database.host->OUT_TXT
    },
    `String: {
        'Error: '->OUT_TXT;
        config->OUT_TXT
    }
};
```

### 21.7.3 Form Data Validation

```walnut
UserRegistration := $[
    username: String<3..20>,
    email: String<5..254>,
    password: String<8..>,
    age: Integer<13..>
];

/* Custom validator */
UserRegistration(data: [username: String<3..20>, email: String<5..254>, password: String<8..>, age: Integer<13..>]) @ String :: {
    ?when(data.email->contains('@') && data.password->length >= 8) {
        data
    } ~ {
        @'Invalid email or password'
    }
};

validateRegistration = ^formData: JsonValue => Result<UserRegistration, String> :: {
    formData->hydrateAs(`UserRegistration)
        @ error :: 'Validation failed: ' + error.message
};

/* Usage */
formData = '{"username": "alice", "email": "alice@example.com", "password": "SecurePass123", "age": 25}'
    ->parseJson;

result = validateRegistration(formData);
```

### 21.7.4 Nested Data Structures

```walnut
Company := [
    id: Integer<1..>,
    name: String<1..>,
    departments: Array<[
        id: Integer<1..>,
        name: String<1..>,
        employees: Array<[
            id: Integer<1..>,
            name: String<1..>,
            email: String,
            role: String['manager', 'developer', 'designer']
        ]>
    ]>
];

jsonData = /* complex nested JSON */;
company = jsonData->parseJson->hydrateAs(`Company);

?whenTypeOf(company) is {
    `Company: {
        company.departments->forEach(^dept :: {
            dept.name->OUT_TXT;
            ': '->OUT_TXT;
            dept.employees->length->asString->OUT_TXT;
            ' employees'->OUT_LN
        })
    },
    `HydrationError: {
        'Error at '->OUT_TXT;
        company.path->joined('.')
    }
};
```

### 21.7.5 Versioned Data Migration

```walnut
/* Multiple versions of data format */
UserV1 := [id: Integer, name: String];
UserV2 := [id: Integer, firstName: String, lastName: String];
UserV3 := [id: Integer, firstName: String, lastName: String, email: String];

loadUser = ^jsonData: JsonValue => Result<UserV3, String> :: {
    /* Try latest version first */
    v3 = jsonData->hydrateAs(`UserV3);
    ?whenTypeOf(v3) is {
        `UserV3: v3,
        ~: {
            /* Try V2 */
            v2 = jsonData->hydrateAs(`UserV2);
            ?whenTypeOf(v2) is {
                `UserV2: [
                    id: v2.id,
                    firstName: v2.firstName,
                    lastName: v2.lastName,
                    email: ''  /* Default for missing field */
                ],
                ~: {
                    /* Try V1 */
                    v1 = jsonData->hydrateAs(`UserV1);
                    ?whenTypeOf(v1) is {
                        `UserV1: {
                            parts = v1.name->split(' ');
                            [
                                id: v1.id,
                                firstName: parts->item(0),
                                lastName: ?whenIsError(parts->item(1)) { '' },
                                email: ''
                            ]
                        },
                        ~: @'Cannot parse user data'
                    }
                }
            }
        }
    }
};
```

## 21.8 Best Practices

### 21.8.1 Use Strong Types

```walnut
/* Good: Precise types with constraints */
User := $[
    id: Integer<1..>,
    email: String<5..254>,
    age: Integer<0..150>,
    status: String['active', 'inactive', 'suspended']
];

/* Avoid: Loose types */
/* User := [id: Integer, email: String, age: Integer, status: String]; */
```

### 21.8.2 Provide Clear Error Messages

```walnut
/* Good: Descriptive validator errors */
Email := #String;

Email(value: String) @ String :: {
    ?when(value->matches('^[^@]+@[^@]+\\.[^@]+$')) {
        value
    } ~ {
        @'Email must be in format: user@domain.com'
    }
};

/* Avoid: Generic errors */
/* @'Invalid value' */
```

### 21.8.3 Handle Optional Fields Explicitly

```walnut
/* Good: Explicit optional fields */
User := [
    id: Integer<1..>,
    name: String<1..>,
    email?: String,      /* Optional */
    phone?: String       /* Optional */
];

/* Handle missing values */
user = jsonData->parseJson->hydrateAs(`User);
?whenTypeOf(user) is {
    `User: {
        emailDisplay = ?whenIsError(user.email) { 'No email provided' };
        emailDisplay->OUT_TXT
    }
};
```

### 21.8.4 Use Sealed Records for Strict Validation

```walnut
/* Good: Sealed record rejects unexpected fields */
StrictConfig := $[
    host: String,
    port: Integer,
    debug: Boolean
];

/* Any extra fields in JSON will cause hydration error */
```

### 21.8.5 Compose Types for Reusability

```walnut
/* Good: Reusable components */
EmailAddress := String<5..254>;
PhoneNumber := String<10..15>;
UserId := Integer<1..>;

User := [
    id: UserId,
    email: EmailAddress,
    phone?: PhoneNumber
];

Admin := [
    id: UserId,
    email: EmailAddress,
    permissions: Array<String>
];

/* Types are validated consistently */
```

### 21.8.6 Validate Early

```walnut
/* Good: Validate at system boundaries */
handleRequest = ^request: HttpRequest => HttpResponse :: {
    /* Hydrate and validate immediately */
    userData = request.body->parseJson->hydrateAs(`UserInput);

    ?whenTypeOf(userData) is {
        `UserInput: processUser(userData),
        `HydrationError: HttpResponse([
            status: 400,
            body: [error: userData.message]->toJson
        ])
    }
};

/* Rest of code works with validated types */
```

### 21.8.7 Use JsonValue Casts for Format Conversion

```walnut
/* Good: Handle different input formats */
Date := #[year: Integer, month: Integer, day: Integer];

Date.JsonValue = ^JsonValue => Result<Date, HydrationError> :: {
    ?whenTypeOf($) is {
        `JsonValueString: {
            /* Parse "YYYY-MM-DD" format */
            parts = $.value->split('-');
            ?when(parts->length == 3) {
                Date([
                    year: parts->item(0)->parseInt,
                    month: parts->item(1)->parseInt,
                    day: parts->item(2)->parseInt
                ])
            } ~ {
                @HydrationError([
                    message: 'Date must be in YYYY-MM-DD format',
                    path: [],
                    expectedType: 'Date',
                    actualValue: $
                ])
            }
        },
        `JsonValueObject: {
            /* Parse object format */
            $->hydrateAs(`[year: Integer, month: Integer, day: Integer])
                @> Date
        },
        ~: @HydrationError([
            message: 'Expected string or object',
            path: [],
            expectedType: 'Date',
            actualValue: $
        ])
    }
};

/* Now supports both formats */
date1 = '"2025-01-15"'->parseJson->hydrateAs(`Date);
date2 = '{"year": 2025, "month": 1, "day": 15}'->parseJson->hydrateAs(`Date);
```

## 21.9 Performance Considerations

### 21.9.1 Hydration Cost

Hydration involves runtime type checking and validation, which has performance implications:

- **Type checking**: Each field/element is checked against its expected type
- **Range validation**: Numeric and string ranges are validated
- **Validator execution**: Custom validators run for each value
- **Error construction**: Detailed error messages are built when validation fails

### 21.9.2 Optimization Strategies

**1. Cache hydrated types:**
```walnut
/* Good: Hydrate once, reuse many times */
config = loadConfig()->parseJson->hydrateAs(`Config);
/* config is now validated, use it throughout the application */
```

**2. Use appropriate constraint granularity:**
```walnut
/* Good: Reasonable constraints */
Age := Integer<0..150>;

/* Avoid: Overly specific constraints that add little value */
/* Age := Integer<0..150>[0,1,2,3,...,150]; */  /* Unnecessary */
```

**3. Prefer compile-time checks when possible:**
```walnut
/* Good: Static data can be type-checked at compile time */
user = [id: 1, name: 'Alice'];  /* Type checked at compile time */

/* Reserve hydration for runtime data */
user = externalData->parseJson->hydrateAs(`User);  /* Runtime validation */
```

## Summary

Walnut's hydration system provides:

- **Automatic type conversion** from JsonValue to strongly-typed Walnut values
- **Comprehensive type support** for all Walnut types (primitives, collections, user-defined)
- **Detailed error messages** with path information for debugging
- **Custom hydration logic** via JsonValue cast methods
- **Validator integration** for complex business rules
- **Type safety** at runtime boundaries

Key features:
- Hydration works recursively for nested structures
- Failed hydration returns `HydrationError` with detailed information
- Validators are automatically executed during hydration
- Custom JsonValue casts allow flexible input format handling
- Union types try alternatives in order
- Optional fields are properly handled

Best practices:
- Use strong, precise types for better validation
- Validate data at system boundaries (API endpoints, file loading)
- Provide clear error messages in validators
- Use sealed records to reject unexpected fields
- Compose reusable type definitions
- Handle hydration errors explicitly with pattern matching

Hydration is essential for:
- API response parsing
- Configuration file loading
- Form data validation
- Database result mapping
- External data integration
- Type-safe data pipelines
