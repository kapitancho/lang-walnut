# 26. Maps and Records

## Overview

Walnut provides two fundamental types for key-value associations:

- **Map**: Dynamic collections with string keys and homogeneous values
- **Record**: Fixed structures with named fields and heterogeneous types

Both types are immutable by default and support type refinements for enhanced type safety.

## 26.1 Map Types

### 26.1.1 Basic Map Type

Maps are collections of key-value pairs where keys are always strings and all values have the same type.

**Syntax:**
```walnut
Map<ValueType>
```

**Examples:**
```walnut
/* Basic maps */
ages = [alice: 30, bob: 25, charlie: 35];     /* Type: Map<Integer> */
scores = [math: 95.5, english: 88.0];         /* Type: Map<Real> */
flags = [debug: true, verbose: false];        /* Type: Map<Boolean> */

/* Empty map */
empty = [:];                                  /* Type: Map<Any> */
```

### 26.1.2 Map Type Refinements

Maps can have constraints on size and value types.

**Size constraints:**
```walnut
/* Fixed size */
ThreeStringValues = Map<String, 3>;           /* Exactly 3 entries */

/* Minimum size */
NonEmptyIntMap = Map<Integer, 1..>;           /* At least 1 entry */

/* Range size */
SmallStringMap = Map<String, 1..10>;          /* 1 to 10 entries */
```

**Value type refinements:**
```walnut
/* Maps with refined value types */
AgeMap = Map<Integer<0..150>>;
ScoreMap = Map<Real<0..100>>;
EmailMap = Map<String<5..254>>;

ages = [alice: 30, bob: 25];                  /* Type: AgeMap */
scores = [test1: 95.5, test2: 88.0];          /* Type: ScoreMap */
```

### 26.1.3 Map Literals

**Syntax:**
```walnut
[key1: value1, key2: value2, key3: value3]
[:]                                           /* Empty map */
```

**Examples:**
```walnut
/* String keys, integer values */
population = [
    london: 9000000,
    paris: 2200000,
    berlin: 3700000
];

/* Nested maps */
config = [
    database: [host: 'localhost', port: 5432],
    cache: [enabled: true, ttl: 3600]
];
/* Type: Map<Map<Integer | String | Boolean>> */

/* Computed keys (using string expressions) */
key = 'dynamic';
map = [(key): 42];                            /* [dynamic: 42] */

/* Trailing comma */
data = [
    first: 1,
    second: 2,
    third: 3,
];
```

## 26.2 Map Operations

### 26.2.1 Basic Properties

**`length` - Get map size**
```walnut
/* Signature */
^Map<T> => Integer

/* Examples */
[a: 1, b: 2, c: 3]->length;                   /* 3 */
[:]->length;                                  /* 0 */
```

**`item(key)` - Access value by key**
```walnut
/* Signature */
^[Map<T>, String] => T | Null

/* Examples */
ages = [alice: 30, bob: 25, charlie: 35];
ages->item('alice');                          /* 30 */
ages->item('bob');                            /* 25 */
ages->item('david');                          /* null (key doesn't exist) */
```

**`keys` - Get all keys**
```walnut
/* Signature */
^Map<T> => Array<String>

/* Examples */
[a: 1, b: 2, c: 3]->keys;                     /* ['a', 'b', 'c'] */
[:]->keys;                                    /* [] */

/* Keys are returned in insertion order */
[z: 26, a: 1, m: 13]->keys;                   /* ['z', 'a', 'm'] */
```

**`values` - Get all values**
```walnut
/* Signature */
^Map<T> => Array<T>

/* Examples */
[a: 1, b: 2, c: 3]->values;                   /* [1, 2, 3] */
[alice: 30, bob: 25]->values;                 /* [30, 25] */
```

**`itemPairs` - Get key-value pairs**
```walnut
/* Signature */
^Map<T> => Array<(String, T)>

/* Examples */
[a: 1, b: 2]->itemPairs;                      /* [('a', 1), ('b', 2)] */

/* Useful for iteration */
map = [alice: 30, bob: 25];
map->itemPairs->forEach(^pair :: {
    (key, value) = pair;
    (key + ' is ' + value->asString)->printed;
});
```

### 26.2.2 Searching

**`contains(key)` - Check if key exists**
```walnut
/* Signature */
^[Map<T>, String] => Boolean

/* Examples */
ages = [alice: 30, bob: 25];
ages->contains('alice');                      /* true */
ages->contains('charlie');                    /* false */
```

**`containsValue(value)` - Check if value exists**
```walnut
/* Signature */
^[Map<T>, T] => Boolean

/* Examples */
ages = [alice: 30, bob: 25, charlie: 30];
ages->containsValue(30);                      /* true */
ages->containsValue(40);                      /* false */
```

**`findKey(predicate)` - Find first key matching predicate**
```walnut
/* Signature */
^[Map<T>, ^T => Boolean] => String | Null

/* Examples */
ages = [alice: 30, bob: 25, charlie: 35];
ages->findKey(^# >= 30);                      /* 'alice' */
ages->findKey(^# > 100);                      /* null */
```

### 26.2.3 Modification

**`with(key, value)` - Add or update entry**
```walnut
/* Signature */
^[Map<T>, String, T] => Map<T>

/* Examples */
ages = [alice: 30, bob: 25];
ages->with('charlie', 35);                    /* [alice: 30, bob: 25, charlie: 35] */
ages->with('alice', 31);                      /* [alice: 31, bob: 25] (updated) */

/* Chain multiple updates */
[:]
    ->with('a', 1)
    ->with('b', 2)
    ->with('c', 3);                           /* [a: 1, b: 2, c: 3] */
```

**`without(key)` - Remove entry**
```walnut
/* Signature */
^[Map<T>, String] => Map<T>

/* Examples */
ages = [alice: 30, bob: 25, charlie: 35];
ages->without('bob');                         /* [alice: 30, charlie: 35] */
ages->without('david');                       /* [alice: 30, bob: 25, charlie: 35] (no change) */

/* Chain multiple removals */
ages->without('alice')->without('bob');       /* [charlie: 35] */
```

**`merge(other)` - Merge two maps**
```walnut
/* Signature */
^[Map<T>, Map<T>] => Map<T>

/* Examples */
map1 = [a: 1, b: 2];
map2 = [b: 20, c: 3];
map1->merge(map2);                            /* [a: 1, b: 20, c: 3] */
                                              /* map2 values override map1 */

/* Merge multiple maps */
[a: 1]->merge([b: 2])->merge([c: 3]);         /* [a: 1, b: 2, c: 3] */
```

### 26.2.4 Transformation

**`map(transform)` - Transform values**
```walnut
/* Signature */
^[Map<T>, ^T => U] => Map<U>

/* Examples */
ages = [alice: 30, bob: 25, charlie: 35];
ages->map(^# + 1);                            /* [alice: 31, bob: 26, charlie: 36] */

prices = [apple: 1.99, banana: 0.99];
prices->map(^# * 1.1);                        /* Apply 10% increase */
```

**`mapKeyValue(transform)` - Transform with key and value**
```walnut
/* Signature */
^[Map<T>, ^[String, T] => U] => Map<U>

/* Examples */
data = [a: 1, b: 2, c: 3];
data->mapKeyValue(^[#key, #value] ::
    #key->toUpperCase + #value->asString
);                                            /* [a: 'A1', b: 'B2', c: 'C3'] */

/* Create descriptions */
ages = [alice: 30, bob: 25];
ages->mapKeyValue(^[#key, #value] ::
    #key + ' is ' + #value->asString + ' years old'
);
/* [alice: 'alice is 30 years old', bob: 'bob is 25 years old'] */
```

**`filter(predicate)` - Keep matching entries**
```walnut
/* Signature */
^[Map<T>, ^T => Boolean] => Map<T>

/* Examples */
ages = [alice: 30, bob: 25, charlie: 35, david: 28];
ages->filter(^# >= 30);                       /* [alice: 30, charlie: 35] */

scores = [test1: 95.5, test2: 78.0, test3: 88.0];
scores->filter(^# >= 80.0);                   /* [test1: 95.5, test3: 88.0] */
```

**`filterKeyValue(predicate)` - Filter with key and value**
```walnut
/* Signature */
^[Map<T>, ^[String, T] => Boolean] => Map<T>

/* Examples */
ages = [alice: 30, bob: 25, charlie: 35];
ages->filterKeyValue(^[#key, #value] ::
    #key->startsWith('a') && #value >= 30
);                                            /* [alice: 30] */
```

**`reduce(initial, accumulator)` - Reduce to single value**
```walnut
/* Signature */
^[Map<T>, U, ^[U, T] => U] => U

/* Examples */
/* Sum all values */
[a: 10, b: 20, c: 30]->reduce(0, ^[#acc, #val] :: #acc + #val);
/* 60 */

/* Concatenate all values */
[first: 'Hello', second: 'World']->reduce('', ^[#acc, #val] ::
    ?when(#acc == '') { #val } ~ { #acc + ' ' + #val }
);                                            /* 'Hello World' */
```

**`reduceKeyValue(initial, accumulator)` - Reduce with keys**
```walnut
/* Signature */
^[Map<T>, U, ^[U, String, T] => U] => U

/* Examples */
prices = [apple: 1.99, banana: 0.99, orange: 1.49];
prices->reduceKeyValue('', ^[#acc, #key, #val] ::
    #acc + #key + ': $' + #val->asString + '\n'
);
/* 'apple: $1.99\nbanana: $0.99\norange: $1.49\n' */
```

### 26.2.5 Aggregation

**`all(predicate)` - Check if all values match**
```walnut
/* Signature */
^[Map<T>, ^T => Boolean] => Boolean

/* Examples */
scores = [test1: 85, test2: 90, test3: 92];
scores->all(^# >= 80);                        /* true */
scores->all(^# >= 90);                        /* false */
```

**`any(predicate)` - Check if any value matches**
```walnut
/* Signature */
^[Map<T>, ^T => Boolean] => Boolean

/* Examples */
scores = [test1: 85, test2: 70, test3: 92];
scores->any(^# >= 90);                        /* true */
scores->any(^# < 60);                         /* false */
```

**`count(predicate)` - Count matching values**
```walnut
/* Signature */
^[Map<T>, ^T => Boolean] => Integer

/* Examples */
ages = [alice: 30, bob: 25, charlie: 35, david: 28];
ages->count(^# >= 30);                        /* 2 */
ages->count(^# < 25);                         /* 0 */
```

### 26.2.6 Conversion

**`toArray(transform)` - Convert to array**
```walnut
/* Signature */
^[Map<T>, ^[String, T] => U] => Array<U>

/* Examples */
ages = [alice: 30, bob: 25, charlie: 35];

/* Extract values */
ages->toArray(^[#key, #value] :: #value);     /* [30, 25, 35] */

/* Create descriptions */
ages->toArray(^[#key, #value] ::
    #key + ': ' + #value->asString
);                                            /* ['alice: 30', 'bob: 25', 'charlie: 35'] */

/* Create records */
ages->toArray(^[#key, #value] ::
    [name: #key, age: #value]
);
/* [[name: 'alice', age: 30], [name: 'bob', age: 25], ...] */
```

## 26.3 Record Types

### 26.3.1 Basic Record Type

Records are fixed structures with named fields where each field can have a different type.

**Syntax:**
```walnut
[field1: Type1, field2: Type2, field3: Type3]
```

**Examples:**
```walnut
/* Basic records */
person = [name: 'Alice', age: 30, active: true];
/* Type: [name: String, age: Integer, active: Boolean] */

point = [x: 10, y: 20];
/* Type: [x: Integer, y: Integer] */

product = [id: 1, name: 'Widget', price: 19.99];
/* Type: [id: Integer, name: String, price: Real] */

/* Empty record */
empty = [];
/* Type: [] - unit record */
```

### 26.3.2 Record Type Aliases

Records can have type aliases for better semantics and reuse.

**Examples:**
```walnut
/* Type definitions */
Person = [name: String, age: Integer, email: String];
Point2D = [x: Integer, y: Integer];
Product = [id: Integer, name: String, price: Real, inStock: Boolean];

/* Usage */
alice = [name: 'Alice', age: 30, email: 'alice@example.com'];  /* Type: Person */
origin = [x: 0, y: 0];                                          /* Type: Point2D */
widget = [id: 1, name: 'Widget', price: 19.99, inStock: true]; /* Type: Product */
```

### 26.3.3 Optional Fields

Record fields can be optional using the `?` type modifier.

**Examples:**
```walnut
/* Type with optional fields */
User = [
    name: String,
    age: Integer,
    email: ?String,        /* Optional */
    phone: ?String         /* Optional */
];

/* Create records with optional fields */
user1 = [name: 'Alice', age: 30, email: 'alice@example.com'];                       /* Type: User */
user2 = [name: 'Bob', age: 25, email: null, phone: null];                           /* Type: User */
user3 = [name: 'Charlie', age: 35, email: 'charlie@example.com', phone: '555-1234'];/* Type: User */
```

### 26.3.4 OptionalKey Type

The `OptionalKey<T>` type (short syntax: `?T`) represents record fields that may or may not be present.

**Key characteristics:**
- Fields with `OptionalKey<T>` may be missing from the record
- Accessing such fields returns `Result<T, MapItemNotFound>`
- The short syntax `?T` is equivalent to `OptionalKey<T>`

**Syntax:**
```walnut
/* Long form */
Record = [field: OptionalKey<Type>, ...];

/* Short form (preferred) */
Record = [field: ?Type, ...];
```

**Examples:**
```walnut
/* Type definitions */
User = [
    name: String,
    age: Integer,
    email: ?String,        /* Optional - may be missing */
    phone: ?String         /* Optional - may be missing */
];

Config = [
    host: String,
    port: Integer,
    debug: ?Boolean        /* Optional debug flag */
];
```

**Property access returns Result:**
```walnut
/* Accessing optional key returns Result */
getEmail = ^User => Result<String, MapItemNotFound> :: $.email;

/* Or using item method */
getEmail2 = ^User => Result<String, MapItemNotFound> :: $->item('email');

/* Handle with pattern matching */
displayEmail = ^User => String :: {
    ?whenTypeOf($.email) is {
        `String: 'Email: ' + $.email,
        `MapItemNotFound: 'No email provided'
    }
};
```

**Creating records with optional fields:**
```walnut
User = [name: String, age: Integer, email: ?String];

/* With optional field present */
user1 = [name: 'Alice', age: 30, email: 'alice@example.com'];
email1 = user1.email;  /* Result<String, MapItemNotFound> containing 'alice@example.com' */

/* Without optional field */
user2 = [name: 'Bob', age: 25];
email2 = user2.email;  /* Result<String, MapItemNotFound> containing MapItemNotFound */
```

**Subtyping relationships:**
```walnut
/* For any type T: T <: OptionalKey<T> */
/* This means a required field is compatible with an optional field */
RequiredField = [name: String];
OptionalField = [name: ?String];

/* RequiredField is a subtype of OptionalField */
isSubtype = type{RequiredField}->isSubtypeOf(type{OptionalField});  /* true */

/* If T <: R, then OptionalKey<T> <: OptionalKey<R> */
/* This preserves type relationships */
type{?Integer<1..10>}->isSubtypeOf(type{?Integer});  /* true */
```

**Working with optional fields:**
```walnut
User = [name: String, email: ?String, phone: ?String];

/* Extract optional value with fallback */
getUserEmail = ^User => String :: {
    ?whenIsError($.email) { 'no-email@example.com' }
};

/* Check if optional field is present */
hasEmail = ^User => Boolean :: {
    ?whenTypeOf($.email) is {
        `String: true,
        `MapItemNotFound: false
    }
};

/* Collect all present optional fields */
getContactMethods = ^User => Array<String> :: {
    methods = mutable{Array<String>, []};

    ?whenTypeOf($.email) is {
        `String: methods->PUSH('Email: ' + $.email)
    };

    ?whenTypeOf($.phone) is {
        `String: methods->PUSH('Phone: ' + $.phone)
    };

    methods->value
};
```

**Optional fields vs Null:**
```walnut
/* OptionalKey<T> - field may be missing */
User1 = [name: String, email: ?String];
user1 = [name: 'Alice'];                    /* email field is missing */
email1 = user1.email;                        /* MapItemNotFound */

/* T | Null - field must be present but value may be null */
User2 = [name: String, email: String | Null];
user2 = [name: 'Bob', email: null];         /* email field is present with null value */
email2 = user2.email;                        /* null */
```

**Rest types with optional fields:**
```walnut
/* Record with both required and optional fields plus rest */
ExtensibleUser = [
    name: String,          /* Required */
    email: ?String,        /* Optional */
    ... String             /* Additional string fields allowed */
];

user = [
    name: 'Alice',
    email: 'alice@example.com',
    city: 'London',        /* Additional field from rest type */
    country: 'UK'          /* Additional field from rest type */
];  /* Type: ExtensibleUser */
```

**Type aliases with OptionalKey:**
```walnut
/* Define reusable optional field types */
OptionalEmail = ?String<5..254>;
OptionalPhone = ?String<10..15>;

ContactInfo = [
    name: String<1..>,
    email: OptionalEmail,
    phone: OptionalPhone
];
```

### 26.3.5 Record Field Refinements

Record fields can have type refinements.

**Examples:**
```walnut
/* Refined field types */
Person = [
    name: String<1..100>,
    age: Integer<0..150>,
    email: String<5..254>
];

Product = [
    id: Integer<1..>,
    name: String<1..>,
    price: Real<0..>,
    quantity: Integer<0..>
];

person = [
    name: 'Alice',
    age: 30,
    email: 'alice@example.com'
];  /* Type: Person */
```

### 26.3.6 Rest Types

Records can have a rest type to allow additional fields.

**Examples:**
```walnut
/* Record with rest type */
BaseUser = [name: String, age: Integer, ..Map<String>];

/* Can have additional string fields */
user = [
    name: 'Alice',
    age: 30,
    city: 'London',
    country: 'UK'
];  /* Type: BaseUser */
```

## 26.4 Record Operations

### 26.4.1 Field Access

Records support direct field access using dot notation.

**Syntax:**
```walnut
record.fieldName
```

**Examples:**
```walnut
person = [name: 'Alice', age: 30, email: 'alice@example.com'];

person.name;                                  /* 'Alice' */
person.age;                                   /* 30 */
person.email;                                 /* 'alice@example.com' */

/* Nested access */
user = [
    name: 'Bob',
    address: [
        street: '123 Main St',
        city: 'London'
    ]
];

user.name;                                    /* 'Bob' */
user.address.street;                          /* '123 Main St' */
user.address.city;                            /* 'London' */
```

### 26.4.2 Record Modification

**`with(field, value)` - Update field value**
```walnut
/* Signature */
^[Record, String, T] => Record

/* Examples */
person = [name: 'Alice', age: 30];
person->with('age', 31);                      /* [name: 'Alice', age: 31] */

/* Chain updates */
person
    ->with('age', 31)
    ->with('name', 'Alicia');                 /* [name: 'Alicia', age: 31] */
```

**`without(field)` - Remove field**
```walnut
/* Signature */
^[Record, String] => Record

/* Examples */
person = [name: 'Alice', age: 30, temp: 'delete me'];
person->without('temp');                      /* [name: 'Alice', age: 30] */
```

**`merge(other)` - Merge records**
```walnut
/* Signature */
^[Record, Record] => Record

/* Examples */
base = [name: 'Alice', age: 30];
extra = [email: 'alice@example.com', phone: '555-1234'];
base->merge(extra);
/* [name: 'Alice', age: 30, email: 'alice@example.com', phone: '555-1234'] */

/* Override fields */
person = [name: 'Alice', age: 30];
updates = [age: 31];
person->merge(updates);                       /* [name: 'Alice', age: 31] */
```

### 26.4.3 Record Properties

**`keys` - Get field names**
```walnut
/* Signature */
^Record => Array<String>

/* Examples */
[name: 'Alice', age: 30]->keys;               /* ['name', 'age'] */
[]->keys;                                     /* [] */
```

**`values` - Get field values**
```walnut
/* Signature */
^Record => Array<Any>

/* Examples */
[name: 'Alice', age: 30]->values;             /* ['Alice', 30] */
[x: 10, y: 20]->values;                       /* [10, 20] */
```

**`itemPairs` - Get field-value pairs**
```walnut
/* Signature */
^Record => Array<(String, Any)>

/* Examples */
[name: 'Alice', age: 30]->itemPairs;          /* [('name', 'Alice'), ('age', 30)] */
```

### 26.4.4 Record Conversion

**Convert record to map**
```walnut
/* Records are structurally similar to maps */
person = [name: 'Alice', age: 30];

/* Access like a map */
person->item('name');                         /* 'Alice' */
person->keys;                                 /* ['name', 'age'] */
person->values;                               /* ['Alice', 30] */
```

## 26.5 Casting and Conversion

### 26.5.1 Map to String

Maps can be converted to strings.

**Examples:**
```walnut
[a: 1, b: 2, c: 3]->asString;                 /* '[a: 1, b: 2, c: 3]' */
[:]->asString;                                /* '[:]' */

/* Nested maps */
[outer: [inner: 42]]->asString;               /* '[outer: [inner: 42]]' */
```

### 26.5.2 Record to String

Records can be converted to strings.

**Examples:**
```walnut
[name: 'Alice', age: 30]->asString;           /* '[name: "Alice", age: 30]' */
[]->asString;                                 /* '[]' */
```

### 26.5.3 Array to Map

Arrays can be converted to maps using a key extractor.

**Examples:**
```walnut
users = [
    [id: 1, name: 'Alice'],
    [id: 2, name: 'Bob']
];

usersById = users->toMap(^#.id->asString);
/* Map<User> with keys '1' and '2' */
```

### 26.5.4 Map to Array

Maps can be converted to arrays using `toArray`.

**Examples:**
```walnut
ages = [alice: 30, bob: 25];

/* Values only */
ages->values;                                 /* [30, 25] */

/* Key-value pairs */
ages->itemPairs;                              /* [('alice', 30), ('bob', 25)] */

/* Custom transformation */
ages->toArray(^[#key, #value] ::
    [name: #key, age: #value]
);
/* [[name: 'alice', age: 30], [name: 'bob', age: 25]] */
```

### 26.5.5 Record to Map

Records cannot be directly cast to Map type since maps have homogeneous values while records have heterogeneous fields. However, records support map-like operations:

**Examples:**
```walnut
person = [name: 'Alice', age: 30];

/* Map-like access */
person->item('name');                         /* 'Alice' */
person->keys;                                 /* ['name', 'age'] */
person->contains('name');                     /* true */
```

### 26.5.6 JSON Conversion

Both maps and records can be converted to/from JSON.

**Examples:**
```walnut
/* Record to JSON */
person = [name: 'Alice', age: 30];
json = person->jsonEncode;                    /* '{"name":"Alice","age":30}' */

/* JSON to record */
jsonStr = '{"name":"Bob","age":25}';
parsed = jsonStr->jsonDecode;                 /* [name: 'Bob', age: 25] */

/* Map to JSON */
ages = [alice: 30, bob: 25];
json = ages->jsonEncode;                      /* '{"alice":30,"bob":25}' */
```

## 26.6 Practical Examples

### 26.6.1 Configuration Management

```walnut
/* Application configuration */
Config = [
    database: [host: String, port: Integer, name: String],
    cache: [enabled: Boolean, ttl: Integer],
    logging: [level: String, file: String]
];

defaultConfig = [
    database: [host: 'localhost', port: 5432, name: 'myapp'],
    cache: [enabled: true, ttl: 3600],
    logging: [level: 'info', file: 'app.log']
];  /* Type: Config */

/* Override specific settings */
prodConfig = defaultConfig->merge([
    database: [host: 'prod.example.com', port: 5432, name: 'myapp_prod'],
    logging: [level: 'warn', file: '/var/log/app.log']
]);
```

### 26.6.2 Data Transformation

```walnut
/* Transform user data */
User = [id: Integer, name: String, email: String, age: Integer];
UserSummary = [id: Integer, name: String];

users = [
    [id: 1, name: 'Alice', email: 'alice@example.com', age: 30],
    [id: 2, name: 'Bob', email: 'bob@example.com', age: 25],
    [id: 3, name: 'Charlie', email: 'charlie@example.com', age: 35]
];  /* Type: Array<User> */

/* Extract summaries */
summaries = users->map(^user ::
    [id: user.id, name: user.name]
);  /* Type: Array<UserSummary> */

/* Index by ID */
usersById = users->toMap(^#.id->asString);
/* Map<User> */

/* Group by age range */
ageRanges = users->reduce(
    [under30: [], over30: []],
    ^[#acc, #user] :: ?when(#user.age < 30) {
        #acc->with('under30', #acc.under30->append(#user))
    } ~ {
        #acc->with('over30', #acc.over30->append(#user))
    }
);
```

### 26.6.3 Building Query Parameters

```walnut
/* Build URL query string from map */
buildQueryString = ^Map<String> => String :: {
    $->toArray(^[#key, #value] ::
        #key + '=' + #value
    )->join('&')
};

params = [
    search: 'walnut',
    category: 'programming',
    page: '1'
];

queryString = buildQueryString(params);
/* 'search=walnut&category=programming&page=1' */
```

### 26.6.4 Validating Records

```walnut
/* Validation functions */
validateEmail = ^String => Boolean ::
    #->contains('@') && #->length >= 5;

validateAge = ^Integer => Boolean ::
    # >= 0 && # <= 150;

validateUser = ^[name: String, email: String, age: Integer] => Array<String> :: {
    mut errors = [];

    ?when($->name->length < 1) {
        PUSH(errors, 'Name is required');
    };

    ?when(!validateEmail($->email)) {
        PUSH(errors, 'Invalid email address');
    };

    ?when(!validateAge($->age)) {
        PUSH(errors, 'Age must be between 0 and 150');
    };

    errors
};

user = [name: 'Alice', email: 'invalid', age: 30];
errors = validateUser(user);                  /* ['Invalid email address'] */
```

### 26.6.5 Merging and Filtering Maps

```walnut
/* Merge multiple configuration sources */
mergeConfigs = ^Array<Map<String>> => Map<String> :: {
    $->reduce([:], ^[#acc, #config] :: #acc->merge(#config))
};

baseConfig = [host: 'localhost', port: '8080'];
envConfig = [host: 'prod.example.com'];
userConfig = [debug: 'true'];

finalConfig = mergeConfigs([baseConfig, envConfig, userConfig]);
/* [host: 'prod.example.com', port: '8080', debug: 'true'] */

/* Filter sensitive data */
filterSensitive = ^Map<String> => Map<String> :: {
    sensitiveKeys = ['password', 'secret', 'token'];
    $->filterKeyValue(^[#key, #value] ::
        !sensitiveKeys->contains(#key)
    )
};

config = [
    username: 'admin',
    password: 'secret123',
    database: 'myapp'
];

publicConfig = filterSensitive(config);
/* [username: 'admin', database: 'myapp'] */
```

### 26.6.6 Building Complex Structures

```walnut
/* Build nested data structures */
OrderItem = [productId: Integer, quantity: Integer, price: Real];
Order = [
    id: Integer,
    customerId: Integer,
    items: Array<OrderItem>,
    total: Real
];

createOrder = ^[customerId: Integer, items: Array<OrderItem>] => Order :: {
    total = items->reduce(0.0, ^[#acc, #item] ::
        #acc + (#item.quantity->asReal * #item.price)
    );

    [
        id: generateOrderId(),
        customerId: customerId,
        items: items,
        total: total
    ]
};

items = [
    [productId: 101, quantity: 2, price: 19.99],
    [productId: 102, quantity: 1, price: 29.99]
];

order = createOrder([customerId: 42, items: items]);
```

## 26.7 Best Practices

### 26.7.1 Use Records for Fixed Structures

```walnut
/* Good: Use records for fixed data structures */
User = [id: Integer, name: String, email: String];
Point2D = [x: Integer, y: Integer];

user = [id: 1, name: 'Alice', email: 'alice@example.com'];  /* Type: User */

/* Avoid: Maps for structured data - loses type information */
```

### 26.7.2 Use Maps for Dynamic Collections

```walnut
/* Good: Use maps for dynamic key-value pairs */
userPreferences = [:];  /* Type: Map<String> - can grow dynamically */
sessionData = [:];      /* Type: Map<Any> - dynamic structure */

/* Avoid: Records for dynamic data - records have fixed structure */
```

### 26.7.3 Define Strong Types

```walnut
/* Good: Define explicit record types */
Person = [
    name: String<1..100>,
    age: Integer<0..150>,
    email: String<5..254>
];

/* Avoid: Inline types */
person = [name: 'Alice', age: 30, email: 'alice@example.com'];
```

### 26.7.4 Use Optional Fields Appropriately

```walnut
/* Good: Optional fields for truly optional data */
User = [
    name: String,
    email: String,
    phone: ?String,        /* Optional */
    website: ?String       /* Optional */
];

/* Avoid: Required fields that might be missing */
```

### 26.7.5 Validate Map Contents

```walnut
/* Good: Validate map keys and values */
validateConfig = ^Map<String> => Result<Map<String>, String> :: {
    requiredKeys = ['host', 'port', 'database'];

    missingKeys = requiredKeys->filter(^key ::
        !$->contains(key)
    );

    ?when(missingKeys->length > 0) {
        `Error('Missing keys: ' + missingKeys->join(', '))
    } ~ {
        `Ok($)
    }
};

/* Avoid: Assuming keys exist */
config->item('host')->trim;                   /* May fail if 'host' missing */
```

### 26.7.6 Use Type Refinements

```walnut
/* Good: Use size constraints for maps */
NonEmptyIntMap = Map<Integer, 1..>;
SmallStringCache = Map<String, 1..100>;

cache = [:];  /* Type: Map<String> */

/* Use type refinements when constraints matter */
```

### 26.7.7 Prefer Immutable Operations

```walnut
/* Good: Use immutable operations */
updatedMap = oldMap->with('key', 'value');
mergedMap = map1->merge(map2);

/* Avoid: Mutable operations (not available for maps/records) */
```

## 26.8 Performance Considerations

### 26.8.1 Map Lookups

Map lookups by key are efficient (typically O(1)):

```walnut
/* Efficient key lookup */
value = map->item('key');

/* Efficient key existence check */
exists = map->contains('key');
```

### 26.8.2 Map Transformations

Map transformations create new maps. For multiple operations, chain them efficiently:

```walnut
/* Good: Chain operations */
result = originalMap
    ->filter(^# > 0)
    ->map(^# * 2)
    ->with('extra', 42);

/* Avoid: Multiple intermediate variables */
filtered = originalMap->filter(^# > 0);
mapped = filtered->map(^# * 2);
result = mapped->with('extra', 42);
```

### 26.8.3 Record Field Access

Direct field access is more efficient than dynamic lookups:

```walnut
/* Good: Direct field access */
name = person.name;
age = person.age;

/* Avoid: Dynamic access when field is known */
name = person->item('name');
```

### 26.8.4 Building Large Maps

For building large maps, consider using `reduce` or chaining `with`:

```walnut
/* Efficient map building */
items = [/* large array */];
itemMap = items->reduce([:], ^[#acc, #item] ::
    #acc->with(#item.id->asString, #item)
);
```

## Summary

Walnut's map and record system provides:

**Maps:**
- **Dynamic collections** with string keys and homogeneous values
- **Type refinements** for size and value constraints
- **Immutable operations**: with, without, merge
- **Rich transformations**: map, filter, reduce
- **Aggregation functions**: all, any, count
- **Conversion to arrays** with custom transformations

**Records:**
- **Fixed structures** with named fields and heterogeneous types
- **Direct field access** using dot notation
- **Optional fields** with `?` type modifier
- **Type refinements** for field values
- **Rest types** for extensible records
- **Map-like operations** available

**Key Features:**
- Type-safe operations
- Compile-time type checking
- Immutability by default
- Integration with other collection types
- JSON serialization support
- Structural typing

Best practices include:
- Use records for fixed structures
- Use maps for dynamic collections
- Define strong types with refinements
- Use optional fields appropriately
- Validate map contents
- Prefer immutable operations
- Choose efficient access patterns

This comprehensive system enables building complex data structures with strong type safety while maintaining the functional programming principles of immutability and composability.
