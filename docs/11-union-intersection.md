# 27. Union and Intersection Types

## Overview

Walnut provides powerful type composition operators that allow you to combine types in flexible ways:

- **Union types** (`|`) - represent values that can be one of several alternative types
- **Intersection types** (`&`) - represent values that must satisfy all specified types simultaneously

These operators enable precise type modeling, error handling with multiple error types, and flexible function signatures while maintaining type safety.

## 27.1 Union Types

### 27.1.1 Basic Union Types

Union types allow a value to be one of several possible types.

**Syntax:** `Type1 | Type2 | Type3`

**Examples:**
```walnut
/* Basic unions */
IntOrString = Integer | String;
BoolOrNull = Boolean | Null;
NumberType = Integer | Real;

/* Type inference with union-compatible values */
value1 = 42;      /* Type: Integer (compatible with Integer | String) */
value2 = 'hello'; /* Type: String (compatible with Integer | String) */
```

### 27.1.2 Union Type Declarations

Union types can be used in type aliases and function signatures.

**Type aliases:**
```walnut
/* Simple union */
OptionalInt = Integer | Null;

/* Multiple alternatives */
StatusCode = Integer<200..299> | Integer<400..499> | Integer<500..599>;

/* Complex unions */
JsonValue = String | Integer | Real | Boolean | Null |
            Array<JsonValue> | Map<JsonValue>;
```

**Function signatures:**
```walnut
/* Accept multiple input types */
format = ^ value: Integer | String => String :: {
    ?whenTypeOf(value) is {
        `Integer: value->asString,
        `String: value,
        ~: 'unknown'
    }
};

/* Return multiple possible types */
parse = ^ input: String => Integer | String :: {
    result = input->asInteger;
    ?whenTypeOf(result) is {
        `Integer: result,
        `NotANumber: input
    }
};
```

### 27.1.3 Union Types with Type Constraints

Union types can combine constrained types.

**Range unions:**
```walnut
/* Positive or negative integers */
NonZeroInt = Integer<1..> | Integer<..-1>;

/* Multiple numeric ranges */
ValidScore = Integer<0..59> | Integer<60..100>;  /* Fail or pass */

/* Real number ranges */
Temperature = Real<-273.15..> | MinusInfinity;
```

**Enumeration subsets:**
```walnut
Suit = (Hearts, Diamonds, Clubs, Spades);

/* Union of enumeration subsets */
RedOrBlackAce = Suit[Hearts, Diamonds] | Suit[Clubs, Spades];

/* Function using subset union */
cardColor = ^ card: Suit[Hearts, Diamonds] | Suit[Clubs, Spades] => String :: {
    ?whenTypeOf(card) is {
        `Suit[Hearts, Diamonds]: 'red',
        `Suit[Clubs, Spades]: 'black'
    }
};
```

**String subset unions:**
```walnut
HttpMethod = String['GET', 'POST'] | String['PUT', 'DELETE'];
Status = String['pending', 'active'] | String['completed', 'cancelled'];
```

### 27.1.4 Union Types in Records

Records can have fields with union types or be part of union types themselves.

**Fields with union types:**
```walnut
User = [
    id: Integer<1..>,
    name: String,
    email: String | Null,           /* Optional email */
    age: Integer<0..150> | Null     /* Optional age */
];

Config = [
    host: String,
    port: Integer<1..65535>,
    timeout: Integer | Real,         /* Accept both */
    debug: Boolean
];
```

**Union of record types:**
```walnut
/* Discriminated union with shape-based matching */
SuccessResponse = [status: 'success', data: String];
ErrorResponse = [status: 'error', message: String];
Response = SuccessResponse | ErrorResponse;

handleResponse = ^ ~Response => String :: {
    ?whenTypeOf(response) is {
        `[status: 'success', data: String]: response.data,
        `[status: 'error', message: String]: 'Error: ' + response.message
    }
};
```

**Rest type unions:**
```walnut
/* Record with rest type union */
MixedData = [id: Integer, ... String | Boolean];

data = [id: 1, name: 'Alice', active: true];  /* Type: MixedData */
```

### 27.1.5 Union Types in Collections

Collections can have union element types.

**Arrays:**
```walnut
/* Array with mixed element types */
MixedArray = Array<Integer | String>;

items = [1, 'two', 3, 'four'];  /* Type: MixedArray */

/* Process mixed array */
process = ^ arr: Array<Integer | String> => Array<String> :: {
    arr->map(^item => String :: {
        ?whenTypeOf(item) is {
            `Integer: item->asString,
            `String: item
        }
    })
};
```

**Maps:**
```walnut
/* Map with union value type */
ConfigMap = Map<String | Integer | Boolean>;

config = [
    host: 'localhost',
    port: 3000,
    debug: true
];  /* Type: ConfigMap */

/* Map with union key type */
MixedKeyMap = Map<String | Integer, 1..10>;

/* Merging maps with different value types */
merge = ^[map1: Map<String>, map2: Map<Integer>] => Map<String | Integer> :: {
    #map1->mergeWith(#map2)
};
```

**Tuples:**
```walnut
/* Tuple with union element types */
MixedTuple = [Integer | String, Boolean, Real | Null];

tuple = [42, true, 3.14];  /* Type: MixedTuple */
tuple2 = ['hello', false, null];  /* Type: MixedTuple */
```

## 27.2 Intersection Types

### 27.2.1 Basic Intersection Types

Intersection types represent values that satisfy all specified types simultaneously.

**Syntax:** `Type1 & Type2 & Type3`

**Examples:**
```walnut
/* Enumeration subset intersection */
Suit = (Hearts, Diamonds, Clubs, Spades);

/* Intersection of subsets */
DiamondOnly = Suit[Hearts, Diamonds] & Suit[Clubs, Diamonds];
/* Result: Suit[Diamonds] - only the common element */

redCard = ^ ~DiamondOnly => String :: 'red';
```

### 27.2.2 Record Intersection

Intersection types are most commonly used with records to merge type definitions.

**Basic record intersection:**
```walnut
/* Base types */
Person = [name: String<1..>, age: Integer<1..>, ...];
Employee = [name: String<1..>, position: String<1..>, salary: Integer<0..>, ...];

/* Intersection requires both */
PersonEmployee = Person & Employee;

/* Must have all fields from both types */
getName = ^ person: Person & Employee => String<1..> :: person.name;
getSalary = ^ employee: Person & Employee => Integer<0..> :: employee.salary;

getData = ^ person: Person & Employee => [
    name: String<1..>,
    age: Integer<1..>,
    position: String<1..>,
    salary: Integer<0..>
] :: {
    [
        name: person.name,
        age: person.age,
        position: person.position,
        salary: person.salary
    ]
};
```

**Type composition pattern:**
```walnut
/* Build complex types from simple ones */
HasId = [id: Integer<1..>, ...];
HasTimestamps = [createdAt: Integer, updatedAt: Integer, ...];
HasName = [name: String<1..>, ...];

/* Compose types */
Entity = HasId & HasTimestamps;
User = Entity & HasName & [email: String, ...];

createUser = ^ ~User => Null :: {
    /* Value must have: id, createdAt, updatedAt, name, email */
};
```

### 27.2.3 Intersection with Field Type Refinement

When records with overlapping fields are intersected, the field types are intersected.

**Overlapping fields:**
```walnut
R1 = [a: Integer<5..20>, b: Real, ...];
R2 = [a: Integer<14..26>, c: String, ...];

/* Intersection narrows overlapping types */
R3 = R1 & R2;
/* R3 = [a: Integer<14..20>, b: Real, c: String, ...] */
/* Field 'a' is narrowed to Integer<14..20> (intersection of ranges) */

getValue = ^ rec: R3 => Integer<14..20> :: rec.a;
```

**Complex field intersection:**
```walnut
Type1 = [a: String, b: Integer, c: Integer<5..20>, ... Any];
Type2 = [b: Real, c: Integer<10..30>, d: Boolean, ... Any];

Combined = Type1 & Type2;
/* Combined = [
    a: String,
    b: Integer,               // Integer is subtype of Real
    c: Integer<10..20>,       // Intersection of <5..20> and <10..30>
    d: Boolean,
    ... Any
] */

getB = ^ ~Combined => Integer :: combined.b;
getC = ^ ~Combined => Integer<10..20> :: combined.c;
```

### 27.2.4 Intersection with Rest Types

Intersection types with rest types combine the rest type constraints.

**Rest type intersection:**
```walnut
R1 = [a: Integer, ... Integer<0..15>];
R2 = [b: Real<-2..9.3>, ... Integer<0..10>];

Combined = R1 & R2;
/* Combined = [a: Integer, b: Integer<0..9.3>, ... Integer<0..10>] */
/* Rest type is narrowed to Integer<0..10> (intersection) */
```

**Complex rest intersection:**
```walnut
getTricky = ^ rec: [
    a: Integer<5..20>,
    b: Real,
    c: Integer<4..22>,
    ... Integer<0..15>
] & [
    a: Integer<14..26>,
    d: Integer<1..8>,
    ... Integer<0..10>
] => [
    a: Integer<14..20>,
    b: Integer<0..10>,
    c: Integer<4..10>,
    d: Integer<1..8>,
    ... Integer<0..10>
] :: rec;
```

### 27.2.5 Intersection Types in Practice

**Mixin pattern:**
```walnut
/* Define capabilities as separate types */
Serializable = [toJson: ^Null => String, ...];
Comparable = [compareTo: ^$ => Integer, ...];
Printable = [toString: ^Null => String, ...];

/* Compose types with multiple capabilities */
FullEntity = Entity & Serializable & Comparable & Printable;

serialize = ^ ~Serializable => String :: serializable.toJson();
compare = ^[obj1: Comparable, obj2: Comparable] => Boolean :: {
    #obj1->compareTo(#obj2) < 0
};
```

## 27.3 Type Narrowing and Pattern Matching

### 27.3.1 Type Guards with ?whenTypeOf

Pattern matching with union types requires type guards to narrow the type.

**Basic type narrowing:**
```walnut
process = ^ value: Integer | String => String :: {
    ?whenTypeOf(value) is {
        `Integer: 'Number: ' + value->asString,
        `String: 'Text: ' + value,
        ~: 'Unknown'
    }
};

result1 = process(42);      /* 'Number: 42' */
result2 = process('hello'); /* 'Text: hello' */
```

**Narrowing with type literals:**
```walnut
getValue = ^ value: Integer | String | Boolean => String :: {
    ?whenTypeOf(value) is {
        type{Integer}: 'int: ' + value->asString,
        type{String}: 'str: ' + value,
        type{Boolean}: 'bool: ' + value->asString
    }
};
```

### 27.3.2 Discriminated Unions

Use record shape matching for discriminated unions.

**Status-based discrimination:**
```walnut
SuccessResult = [status: 'success', value: Integer];
ErrorResult = [status: 'error', message: String];
Result = SuccessResult | ErrorResult;

handleResult = ^ ~Result => String :: {
    ?whenTypeOf(result) is {
        `[status: 'success', value: Integer]: {
            'Success: ' + result.value->asString
        },
        `[status: 'error', message: String]: {
            'Error: ' + result.message
        }
    }
};
```

**Type-based discrimination:**
```walnut
Circle = [type: 'circle', radius: Real<0..>];
Rectangle = [type: 'rectangle', width: Real<0..>, height: Real<0..>];
Shape = Circle | Rectangle;

area = ^ ~Shape => Real<0..> :: {
    ?whenTypeOf(shape) is {
        `[type: 'circle', radius: Real<0..>]: {
            3.14159 * shape.radius * shape.radius
        },
        `[type: 'rectangle', width: Real<0..>, height: Real<0..>]: {
            shape.width * shape.height
        }
    }
};
```

### 27.3.3 Error Handling with Union Types

Union types are commonly used for error handling with multiple error types.

**Multiple error types:**
```walnut
NotFound = @NotFound;
InvalidInput = @InvalidInput;
Unauthorized = @Unauthorized;

findUser = ^ userId: Integer => Result<User, NotFound | InvalidInput | Unauthorized> :: {
    ?when(userId <= 0) {
        @InvalidInput
    } ~ ?when(userId > 1000) {
        @NotFound
    } ~ {
        [id: userId, name: 'User ' + userId->asString]
    }
};

/* Handle all error cases */
result = findUser(42);
?whenTypeOf(result) is {
    `User: result.name->OUT_TXT,
    `NotFound: 'User not found'->OUT_TXT,
    `InvalidInput: 'Invalid user ID'->OUT_TXT,
    `Unauthorized: 'Access denied'->OUT_TXT
};
```

**Propagating errors:**
```walnut
ValidationError = @ValidationError;
DatabaseError = @DatabaseError;

createUser = ^[name: String, email: String] =>
    Result<User, ValidationError | DatabaseError> :: {

    ?when(#name->length < 3) {
        @ValidationError
    } ~ ?when(!#email->contains('@')) {
        @ValidationError
    } ~ {
        /* Database operation might fail */
        saveToDatabase([name: #name, email: #email])
    }
};

/* Caller handles union of error types */
userResult = createUser([name: 'Al', email: 'invalid']);
?whenTypeOf(userResult) is {
    `User: 'Created: ' + userResult.name,
    `ValidationError: 'Validation failed',
    `DatabaseError: 'Database error'
};
```

### 27.3.4 Shape-Based Type Narrowing

Use the `->shape` method for runtime type narrowing with union types.

**Shape matching:**
```walnut
HasName = Shape<[name: String, ...]>;
HasTitle = Shape<[title: String, ...]>;
HasNameOrTitle = Shape<[name: String, ...] | [title: String, ...]>;

getDisplayName = ^ obj: HasNameOrTitle => String :: {
    s = obj->shape(`[name: String, ...] | [title: String, ...]);
    ?whenTypeOf(s) is {
        `[name: String, ...]: s.name,
        `[title: String, ...]: s.title
    }
};

/* Works with any value that has name or title */
result1 = getDisplayName([name: 'Alice', age: 30]);
result2 = getDisplayName([title: 'Dr.', surname: 'Smith']);
```

**Function or value discrimination:**
```walnut
NamedRecord = [name: String, ...];
NameProviderRecord = [name: ^Null => String, ...];
HasNameOrFn = Shape<NamedRecord | NameProviderRecord>;

getName = ^ obj: HasNameOrFn => String :: {
    s = obj->shape(`NamedRecord | NameProviderRecord);
    ?whenTypeOf(s) is {
        `NamedRecord: s.name,
        `NameProviderRecord: s.name(),
        ~: 'Unknown'
    }
};
```

## 27.4 Type Reflection

### 27.4.1 Reflecting Union Types

Union types can be reflected at runtime to inspect their structure.

**Union type reflection:**
```walnut
/* Get union member types */
reflectUnion = ^ unionType: Type<Union> => Array<Type> :: unionType->itemTypes;

unionType = type{Integer | String | Boolean};
members = reflectUnion(unionType);
/* Returns: [type{Integer}, type{String}, type{Boolean}] */

/* Check if type is union */
isUnion = unionType->isSubtypeOf(type{Union});  /* true */
```

**Working with reflected types:**
```walnut
checkUnionMember = ^[unionType: Type<Union>, targetType: Type] => Boolean :: {
    members = #unionType->itemTypes;
    members->contains(#targetType)
};

hasString = checkUnionMember([
    type{Integer | String | Boolean},
    type{String}
]);  /* true */
```

### 27.4.2 Reflecting Intersection Types

Intersection types can be similarly reflected.

**Intersection type reflection:**
```walnut
/* Get intersection member types */
reflectIntersection = ^ intersectionType: Type<Intersection> => Array<Type> :: intersectionType->itemTypes;

intersectionType = type{Person & Employee};
members = reflectIntersection(intersectionType);
/* Returns: [type{Person}, type{Employee}] */

/* Check if type is intersection */
isIntersection = intersectionType->isSubtypeOf(type{Intersection});  /* true */
```

### 27.4.3 Type Relationships

Understanding subtype relationships with unions and intersections.

**Union subtyping:**
```walnut
/* A | B is supertype of A and B */
intType = type{Integer};
unionType = type{Integer | String};

intIsSubtypeOfUnion = intType->isSubtypeOf(unionType);  /* true */
unionIsSubtypeOfInt = unionType->isSubtypeOf(intType);  /* false */
```

**Intersection subtyping:**
```walnut
/* A & B is subtype of A and B */
personType = type{Person};
employeeType = type{Employee};
intersectionType = type{Person & Employee};

intersectionIsSubtypeOfPerson = intersectionType->isSubtypeOf(personType);  /* true */
intersectionIsSubtypeOfEmployee = intersectionType->isSubtypeOf(employeeType);  /* true */
personIsSubtypeOfIntersection = personType->isSubtypeOf(intersectionType);  /* false */
```

## 27.5 Practical Examples

### 27.5.1 API Response Handling

```walnut
/* API response types */
ApiSuccess = [status: Integer<200..299>, data: Array<User>];
ApiError = [status: Integer<400..599>, error: String];
ApiResponse = ApiSuccess | ApiError;

handleApiResponse = ^ ~ApiResponse => String :: {
    ?whenTypeOf(apiResponse) is {
        `[status: Integer<200..299>, data: Array<User>]: {
            'Received ' + apiResponse.data->length->asString + ' users'
        },
        `[status: Integer<400..599>, error: String]: {
            'Error ' + apiResponse.status->asString + ': ' + apiResponse.error
        }
    }
};

/* Usage */
successResponse = [status: 200, data: [
    [id: 1, name: 'Alice'],
    [id: 2, name: 'Bob']
]];

errorResponse = [status: 404, error: 'Not found'];

handleApiResponse(successResponse);  /* 'Received 2 users' */
handleApiResponse(errorResponse);    /* 'Error 404: Not found' */
```

### 27.5.2 Event System

```walnut
/* Event types */
ClickEvent = [type: 'click', x: Integer, y: Integer, timestamp: Integer];
KeyEvent = [type: 'keypress', key: String, timestamp: Integer];
ScrollEvent = [type: 'scroll', scrollY: Integer, timestamp: Integer];
Event = ClickEvent | KeyEvent | ScrollEvent;

handleEvent = ^ ~Event => String :: {
    ?whenTypeOf(event) is {
        `[type: 'click', x: Integer, y: Integer, timestamp: Integer]: {
            'Clicked at (' + event.x->asString + ', ' + event.y->asString + ')'
        },
        `[type: 'keypress', key: String, timestamp: Integer]: {
            'Key pressed: ' + event.key
        },
        `[type: 'scroll', scrollY: Integer, timestamp: Integer]: {
            'Scrolled to: ' + event.scrollY->asString
        }
    }
};

/* Event queue */
events = [
    [type: 'click', x: 100, y: 200, timestamp: 1000],
    [type: 'keypress', key: 'Enter', timestamp: 1100],
    [type: 'scroll', scrollY: 500, timestamp: 1200]
];  /* Type: Array<Event> */

events->forEach(^event :: handleEvent(event)->OUT_LN);
```

### 27.5.3 State Machine

```walnut
/* State types */
IdleState = [state: 'idle'];
LoadingState = [state: 'loading', progress: Real<0..100>];
SuccessState = [state: 'success', data: String];
ErrorState = [state: 'error', message: String];
State = IdleState | LoadingState | SuccessState | ErrorState;

/* State transitions */
transition = ^[from: State, action: String] => State :: {
    ?whenTypeOf(from) is {
        `[state: 'idle']: {
            ?when(action == 'start') {
                [state: 'loading', progress: 0.0]
            } ~ {
                from
            }
        },
        `[state: 'loading', progress: Real<0..100>]: {
            ?when(action == 'complete') {
                [state: 'success', data: 'Loaded']
            } ~ ?when(action == 'fail') {
                [state: 'error', message: 'Failed to load']
            } ~ {
                from
            }
        },
        `[state: 'success', data: String]: {
            ?when(action == 'reset') {
                [state: 'idle']
            } ~ {
                from
            }
        },
        `[state: 'error', message: String]: {
            ?when(action == 'retry') {
                [state: 'loading', progress: 0.0]
            } ~ {
                from
            }
        }
    }
};

/* Usage */
state1 = [state: 'idle'];  /* Type: State */
state2 = transition([from: state1, action: 'start']);  /* Loading */
state3 = transition([from: state2, action: 'complete']);  /* Success */
```

### 27.5.4 Validation with Multiple Error Types

```walnut
/* Error types */
TooShort = @TooShort;
TooLong = @TooLong;
InvalidFormat = @InvalidFormat;
InvalidCharacters = @InvalidCharacters;

/* Validation function */
validateUsername = ^ username: String =>
    Result<String, TooShort | TooLong | InvalidFormat | InvalidCharacters> :: {

    ?when(username->length < 3) {
        @TooShort
    } ~ ?when(username->length > 20) {
        @TooLong
    } ~ ?when(!username->matchesRegexp(RegExp('/^[a-zA-Z0-9_]+$/'))) {
        @InvalidCharacters
    } ~ ?when(username->startsWith('_')) {
        @InvalidFormat
    } ~ {
        username
    }
};

/* Handle validation */
handleValidation = ^ username: String => String :: {
    result = validateUsername(username);
    ?whenTypeOf(result) is {
        `String: 'Valid username: ' + result,
        `TooShort: 'Username too short (min 3 characters)',
        `TooLong: 'Username too long (max 20 characters)',
        `InvalidFormat: 'Username cannot start with underscore',
        `InvalidCharacters: 'Username contains invalid characters'
    }
};

handleValidation('ab');          /* TooShort */
handleValidation('alice_123');   /* Valid */
handleValidation('_invalid');    /* InvalidFormat */
```

### 27.5.5 Type Composition with Intersection

```walnut
/* Base capabilities */
Identifiable = [id: Integer<1..>, ...];
Timestamped = [createdAt: Integer, updatedAt: Integer, ...];
Versioned = [version: Integer<1..>, ...];
SoftDeletable = [deletedAt: Integer | Null, ...];

/* Compose entity types */
BasicEntity = Identifiable & Timestamped;
VersionedEntity = BasicEntity & Versioned;
FullEntity = VersionedEntity & SoftDeletable;

/* Functions work with composed types */
getEntityId = ^ ~Identifiable => Integer<1..> :: identifiable.id;
getVersion = ^ ~Versioned => Integer<1..> :: versioned.version;
isDeleted = ^ ~SoftDeletable => Boolean :: {
    ?whenTypeOf(softDeletable.deletedAt) is {
        `Null: false,
        `Integer: true
    }
};

/* Create entities */
user = [
    id: 1,
    createdAt: 1609459200,
    updatedAt: 1609459200,
    version: 1,
    deletedAt: null,
    name: 'Alice'
];  /* Type: FullEntity */

id = getEntityId(user);        /* 1 */
ver = getVersion(user);        /* 1 */
deleted = isDeleted(user);     /* false */
```

### 27.5.6 Parser Result Types

```walnut
/* Parser result types */
ParseSuccess = [success: true, value: String, rest: String];
ParseFailure = [success: false, error: String, position: Integer];
ParseResult = ParseSuccess | ParseFailure;

/* Parser combinator */
parseString = ^[input: String, expected: String] => ParseResult :: {
    ?when(input->startsWith(expected)) {
        [
            success: true,
            value: expected,
            rest: input->substring[start: expected->length, length: input->length - expected->length]
        ]
    } ~ {
        [
            success: false,
            error: 'Expected "' + expected + '"',
            position: 0
        ]
    }
};

/* Chain parsers */
parseSequence = ^[input: String, parsers: Array<String>] => ParseResult :: {
    result = mutable{ParseResult, [success: true, value: '', rest: input]};

    parsers->forEach(^expected :: {
        current = result->value;
        ?whenTypeOf(current) is {
            `[success: true, value: String, rest: String]: {
                next = parseString([input: current.rest, expected: expected]);
                ?whenTypeOf(next) is {
                    `[success: true, value: String, rest: String]: {
                        result->SET([
                            success: true,
                            value: current.value + next.value,
                            rest: next.rest
                        ])
                    },
                    `[success: false, error: String, position: Integer]: {
                        result->SET(next)
                    }
                }
            }
        }
    });

    result->value
};

/* Usage */
result = parseSequence([
    input: 'hello world',
    parsers: ['hello', ' ', 'world']
]);

?whenTypeOf(result) is {
    `[success: true, value: String, rest: String]: {
        'Parsed: ' + result.value  /* 'hello world' */
    },
    `[success: false, error: String, position: Integer]: {
        'Parse error at ' + result.position->asString + ': ' + result.error
    }
};
```

## 27.6 Best Practices

### 27.6.1 Use Discriminated Unions

```walnut
/* Good: Discriminated union with type field */
Circle = [type: 'circle', radius: Real<0..>];
Rectangle = [type: 'rectangle', width: Real<0..>, height: Real<0..>];
Shape = Circle | Rectangle;

/* Easy to pattern match */
getShapeType = ^ ~Shape => String :: shape.type;

/* Avoid: Non-discriminated union */
/* Circle = [radius: Real<0..>];
   Rectangle = [width: Real<0..>, height: Real<0..>];
   Shape = Circle | Rectangle;  /* Harder to distinguish */
```

### 27.6.2 Keep Unions Focused

```walnut
/* Good: Focused union for specific purpose */
HttpStatus = Integer<200..299> | Integer<400..499> | Integer<500..599>;

/* Avoid: Too many unrelated alternatives */
/* MixedType = Integer | String | Boolean | Array | Map | ...  /* Too broad */
```

### 27.6.3 Use Intersection for Type Composition

```walnut
/* Good: Compose types with intersection */
HasId = [id: Integer<1..>, ...];
HasTimestamps = [createdAt: Integer, updatedAt: Integer, ...];
Entity = HasId & HasTimestamps;

/* Reusable components */
User = Entity & [name: String, email: String, ...];
Post = Entity & [title: String, content: String, ...];

/* Avoid: Duplicating fields */
/* User = [id: Integer<1..>, createdAt: Integer, updatedAt: Integer, name: String, email: String];
   Post = [id: Integer<1..>, createdAt: Integer, updatedAt: Integer, title: String, content: String]; */
```

### 27.6.4 Handle All Union Cases

```walnut
/* Good: Handle all cases explicitly */
process = ^ value: Integer | String | Boolean => String :: {
    ?whenTypeOf(value) is {
        `Integer: 'int',
        `String: 'string',
        `Boolean: 'bool'
        /* All cases covered */
    }
};

/* Avoid: Missing cases */
/* process = ^Integer | String | Boolean => String :: {
    ?whenTypeOf($) is {
        `Integer: 'int',
        ~: 'other'  /* Boolean and String lumped together */
    }
}; */
```

### 27.6.5 Use Narrow Types in Intersections

```walnut
/* Good: Intersection narrows types */
validateAge = ^ person: [age: Integer<0..150>] & [age: Integer<18..>] => Boolean :: {
    /* person.age is Integer<18..150> here */
    true
};

/* The intersection automatically enforces constraints */
```

### 27.6.6 Prefer Union for Error Types

```walnut
/* Good: Union of specific error types */
Result<User, NotFound | Unauthorized | ValidationError>

/* Allows precise error handling */
?whenTypeOf(result) is {
    `User: processUser(result),
    `NotFound: handle404(),
    `Unauthorized: handle401(),
    `ValidationError: handleValidation()
}

/* Avoid: Generic error type */
/* Result<User, Error>  /* Loses error specificity */
```

### 27.6.7 Document Type Composition

```walnut
/* Good: Clear documentation */
/* Timestamped adds creation and update timestamps */
Timestamped = [createdAt: Integer, updatedAt: Integer, ...];

/* SoftDeletable adds soft delete support */
SoftDeletable = [deletedAt: Integer | Null, ...];

/* Entity combines ID, timestamps, and soft delete */
Entity = Identifiable & Timestamped & SoftDeletable;
```

## Summary

Walnut's union and intersection types provide:

- **Flexible type composition** - combine types in precise ways
- **Type safety** - maintain compile-time checking with composed types
- **Error handling** - model multiple error types precisely
- **Pattern matching** - discriminate between union alternatives
- **Type narrowing** - refine types through intersections
- **Code reuse** - compose reusable type components

Key features:
- **Union (`|`)** - value can be one of several types
- **Intersection (`&`)** - value must satisfy all types
- **Type guards** - `?whenTypeOf` for runtime narrowing
- **Discriminated unions** - record shape-based matching
- **Range intersection** - automatic narrowing of constraints
- **Type reflection** - inspect union/intersection structure at runtime

Common patterns:
- Result types with multiple errors: `Result<T, E1 | E2 | E3>`
- Discriminated unions for state machines
- Mixin pattern with intersection types
- Optional fields: `FieldType | Null`
- API responses with success/error variants
- Type composition for entity types

Best practices:
- Use discriminated unions for easy pattern matching
- Keep unions focused and related
- Compose types with intersection for reusability
- Handle all union cases explicitly
- Document type composition intent
- Prefer union types for multiple error handling
