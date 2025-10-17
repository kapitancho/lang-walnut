# 24. Booleans

## Overview

Walnut's Boolean type represents truth values with two possible values: `true` and `false`. Boolean is actually a built-in enumeration type `(true, false)` with special support for logical operations and type refinement.

## 24.1 Boolean Type

### 24.1.1 Type Definition

The Boolean type is defined as an enumeration:

```walnut
Boolean := (true, false);  /* Conceptually */
```

**Type hierarchy:**
- `Boolean` - the enumeration type containing both `true` and `false`
- `True` - enumeration subset containing only `true` (equivalent to `Boolean[true]`)
- `False` - enumeration subset containing only `false` (equivalent to `Boolean[false]`)

**Type inference:**
```walnut
x = true;   /* Type: True */
y = false;  /* Type: False */
```

### 24.1.2 Boolean Literals

There are exactly two boolean literals:

```walnut
true
false
```

These are not keywords in the traditional sense but enumeration values of the Boolean type.

### 24.1.3 Subtyping Relationships

```walnut
/* Type relationships */
True <: Boolean     /* True is a subtype of Boolean */
False <: Boolean    /* False is a subtype of Boolean */

/* Literal types */
true   /* Inferred type: True */
false  /* Inferred type: False */

/* True is a subtype of Boolean */
/* False is a subtype of Boolean */
```

## 24.2 Logical Operations

### 24.2.1 Logical AND

Boolean conjunction - returns `true` only if both operands are `true`.

```walnut
Boolean->binaryAnd(Boolean => Boolean)

true && true;    /* true */
true && false;   /* false */
false && true;   /* false */
false && false;  /* false */
```

**Examples:**
```walnut
isLoggedIn = true;
hasPermission = false;

canAccess = isLoggedIn && hasPermission;  /* false */

/* Chaining conditions */
isValid = x > 0 && x < 100 && x % 2 == 0;
```

### 24.2.2 Logical OR

Boolean disjunction - returns `true` if at least one operand is `true`.

```walnut
Boolean->binaryOr(Boolean => Boolean)

true || true;    /* true */
true || false;   /* true */
false || true;   /* true */
false || false;  /* false */
```

**Examples:**
```walnut
isAdmin = false;
isOwner = true;

canEdit = isAdmin || isOwner;  /* true */

/* Combine conditions */
hasAccess = isAdmin || isOwner || isModerator;
```

### 24.2.3 Logical XOR

Boolean exclusive or - returns `true` if exactly one operand is `true`.

```walnut
Boolean->binaryXor(Boolean => Boolean)

true ^^ true;    /* false */
true ^^ false;   /* true */
false ^^ true;   /* true */
false ^^ false;  /* false */
```

**Examples:**
```walnut
hasOptionA = true;
hasOptionB = false;

eitherOr = hasOptionA ^^ hasOptionB;  /* true (exactly one) */
```

### 24.2.4 Logical NOT

Boolean negation - returns the opposite value.

```walnut
Boolean->unaryNot(Null => Boolean)

!true;   /* false */
!false;  /* true */
```

**Examples:**
```walnut
isActive = true;
isInactive = !isActive;  /* false */

/* Double negation */
!!true;   /* true */
!!false;  /* false */

/* In conditions */
?when(!isLoggedIn) {
    'Please log in'
} ~ {
    'Welcome'
};
```

## 24.3 Comparison Operations

### 24.3.1 Equality

Test if two boolean values are equal.

```walnut
Boolean->binaryEqual(Boolean => Boolean)

true == true;    /* true */
false == false;  /* true */
true == false;   /* false */
```

### 24.3.2 Inequality

Test if two boolean values are different.

```walnut
Boolean->binaryNotEqual(Boolean => Boolean)

true != false;   /* true */
true != true;    /* false */
false != false;  /* false */
```

## 24.4 Casting From Boolean

### 24.4.1 To Integer

Convert boolean to integer (0 or 1).

```walnut
Boolean->asInteger(Null => Integer<0..1>)

true->asInteger;   /* 1 */
false->asInteger;  /* 0 */
```

**Examples:**
```walnut
/* Count true values */
flags = [true, false, true, true, false];
count = flags->map(^flag => Integer :: flag->asInteger)->sum;  /* 3 */
```

### 24.4.2 To Real

Convert boolean to real number (0.0 or 1.0).

```walnut
Boolean->asReal(Null => Real<0..1>)

true->asReal;   /* 1.0 */
false->asReal;  /* 0.0 */
```

### 24.4.3 To String

Convert boolean to string representation.

```walnut
Boolean->asString(Null => String)

true->asString;   /* 'true' */
false->asString;  /* 'false' */
```

**Examples:**
```walnut
status = true;
message = 'Status: ' + status->asString;  /* 'Status: true' */
```

## 24.5 Casting To Boolean

### 24.5.1 From Integer

Convert integer to boolean: `0` becomes `false`, any other value becomes `true`.

```walnut
Integer->asBoolean(Null => Boolean)

0->asBoolean;    /* false */
1->asBoolean;    /* true */
42->asBoolean;   /* true */
(-5)->asBoolean; /* true */
```

### 24.5.2 From Real

Convert real to boolean: `0.0` becomes `false`, any other value becomes `true`.

```walnut
Real->asBoolean(Null => Boolean)

0.0->asBoolean;   /* false */
1.0->asBoolean;   /* true */
3.14->asBoolean;  /* true */
(-2.5)->asBoolean; /* true */
```

### 24.5.3 From String

Convert string to boolean: empty string becomes `false`, any non-empty string becomes `true`.

```walnut
String->asBoolean(Null => Boolean)

'hello'->asBoolean;  /* true */
'false'->asBoolean;  /* true (non-empty string) */
''->asBoolean;       /* false (empty string) */
' '->asBoolean;      /* true (contains space) */
```

**Note:** The string `'false'` converts to `true` because it's a non-empty string. This is intentional for consistency with the "empty = false" rule.

### 24.5.4 From Collections

Convert collections to boolean: empty collections become `false`, non-empty collections become `true`.

```walnut
Array->asBoolean(Null => Boolean)

[]->asBoolean;       /* false (empty array) */
[1]->asBoolean;      /* true (non-empty) */
[1, 2, 3]->asBoolean; /* true */

Map->asBoolean(Null => Boolean)

[:]->asBoolean;      /* false (empty map) */
[a: 1]->asBoolean;   /* true (non-empty) */

Set->asBoolean(Null => Boolean)

[;]->asBoolean;      /* false (empty set) */
[1;]->asBoolean;     /* true (non-empty) */
```

### 24.5.5 From Any

The universal `asBoolean` method works on any value:

```walnut
Any->asBoolean(Null => Boolean)

/* General rules:
 * - false, 0, 0.0, '', [], [:], [;] → false
 * - Everything else → true
 */

null->asBoolean;     /* Implementation-specific */
true->asBoolean;     /* true */
false->asBoolean;    /* false */
42->asBoolean;       /* true */
0->asBoolean;        /* false */
```

## 24.6 Pattern Matching with Booleans

### 24.6.1 Using ?when

The most common way to work with booleans:

```walnut
?when(condition) {
    /* True branch */
} ~ {
    /* False branch */
};
```

**Examples:**
```walnut
isValid = true;

result = ?when(isValid) {
    'Valid'
} ~ {
    'Invalid'
};

/* Multiple conditions */
?when(x > 0 && y > 0) {
    'Both positive'
} ~ ?when(x > 0) {
    'Only x positive'
} ~ {
    'Neither positive'
};
```

### 24.6.2 Using ?whenValueOf

Pattern match on boolean values:

```walnut
flag = true;

result = ?whenValueOf(flag) is {
    true: 'Enabled',
    false: 'Disabled'
};
```

### 24.6.3 Using ?whenTypeOf

Match on True or False types:

```walnut
?whenTypeOf(value) is {
    `True: 'It is true',
    `False: 'It is false',
    ~: 'Not a boolean'
};
```

### 24.6.4 Using ?whenIsTrue

Special form for boolean checking:

```walnut
?whenIsTrue {
    condition1: 'First true condition',
    condition2: 'Second true condition',
    ~: 'No condition was true'
};
```

## 24.7 Practical Examples

### 24.7.1 Feature Flags

```walnut
FeatureFlags = [
    darkMode: Boolean,
    betaFeatures: Boolean,
    analytics: Boolean
];

flags = [
    darkMode: true,
    betaFeatures: false,
    analytics: true
];

isEnabled = ^feature: String => Boolean :: {
    ?whenIsError(flags->item(feature)) { false }
};

?when(isEnabled('darkMode')) {
    /* Enable dark mode */
};
```

### 24.7.2 Validation

```walnut
User = [
    name: String,
    email: String,
    age: Integer
];

validateUser = ^user: User => [isValid: Boolean, errors: Array<String>] :: {
    errors = mutable{Array<String>, []};

    hasName = user.name->length > 0;
    hasEmail = user.email->contains('@');
    validAge = user.age >= 0 && user.age <= 150;

    ?when(!hasName) { errors->PUSH('Name is required') };
    ?when(!hasEmail) { errors->PUSH('Invalid email') };
    ?when(!validAge) { errors->PUSH('Invalid age') };

    [
        isValid: hasName && hasEmail && validAge,
        errors: errors->value
    ]
};
```

### 24.7.3 Permissions System

```walnut
Permission = (Read, Write, Delete, Admin);

Permissions = Set<Permission>;

User = [
    id: Integer,
    permissions: Permissions
];

hasPermission = ^[user: User, required: Permission] => Boolean :: {
    #user.permissions->contains(Permission.Admin)
    || #user.permissions->contains(#required)
};

canEdit = ^user: User => Boolean :: {
    hasPermission([user: user, required: Permission.Write])
};

canDelete = ^user: User => Boolean :: {
    hasPermission([user: user, required: Permission.Delete])
};
```

### 24.7.4 Boolean Algebra

```walnut
/* De Morgan's Laws */
deMorgan1 = ^[a: Boolean, b: Boolean] => Boolean :: {
    !(#a && #b) == (!#a || !#b)  /* Always true */
};

deMorgan2 = ^[a: Boolean, b: Boolean] => Boolean :: {
    !(#a || #b) == (!#a && !#b)  /* Always true */
};

/* XOR equivalence */
xorEquiv = ^[a: Boolean, b: Boolean] => Boolean :: {
    (#a ^^ #b) == ((#a || #b) && !(#a && #b))  /* Always true */
};
```

### 24.7.5 State Machine

```walnut
State = (Idle, Loading, Success, Error);

canTransition = ^[from: State, to: State] => Boolean :: {
    ?whenValueOf([from: #from, to: #to]) is {
        [from: State.Idle, to: State.Loading]: true,
        [from: State.Loading, to: State.Success]: true,
        [from: State.Loading, to: State.Error]: true,
        [from: State.Success, to: State.Idle]: true,
        [from: State.Error, to: State.Idle]: true,
        ~: false
    }
};
```

### 24.7.6 Filtering and Predicates

```walnut
/* Filter even numbers */
numbers = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
evens = numbers->filter(^n: Integer => Boolean :: n % 2 == 0);
/* [2, 4, 6, 8, 10] */

/* All predicate */
allPositive = ^numbers: Array<Integer> => Boolean :: {
    !{numbers->findFirst(^n: Integer => Boolean :: n <= 0)
        ->isOfType(`ItemNotFound)}
};

/* Any predicate */
anyNegative = ^numbers: Array<Integer> => Boolean :: {
    numbers->findFirst(^n: Integer => Boolean :: n < 0)
        ->isOfType(`Integer)
};
```

### 24.7.7 Toggle Function

```walnut
/* Toggle boolean value */
toggle = ^value: Boolean => Boolean :: {
    !value
};

/* Toggle with state */
Toggleable = [enabled: Mutable<Boolean>];

createToggle = ^initialState: Boolean => Toggleable :: {
    [enabled: mutable{Boolean, initialState}]
};

toggleState = ^t: Toggleable => Null :: {
    t.enabled->SET(!t.enabled->value)
};

/* Usage */
darkMode = createToggle(false);
toggleState(darkMode);  /* Now true */
toggleState(darkMode);  /* Now false */
```

## 24.8 Best Practices

### 24.8.1 Use Descriptive Variable Names

```walnut
/* Good: Descriptive boolean names */
isValid = true;
hasPermission = false;
canEdit = true;
shouldUpdate = false;

/* Avoid: Ambiguous names */
/* flag = true; */
/* status = false; */
```

### 24.8.2 Prefer Early Returns

```walnut
/* Good: Early return for negative case */
validateInput = ^input: String => Result<String, String> :: {
    ?when(input->length == 0) {
        => @'Input cannot be empty'
    };

    ?when(input->length > 100) {
        => @'Input too long'
    };

    input  /* Valid */
};

/* Avoid: Deep nesting */
/* ?when(input->length > 0) {
    ?when(input->length <= 100) {
        input
    } ~ { @'Too long' }
} ~ { @'Empty' }; */
```

### 24.8.3 Use Type-Specific Booleans

```walnut
/* Good: Use True/False when appropriate */
alwaysTrue = ^=> True :: true;
alwaysFalse = ^=> False :: false;

/* Function that must return true */
validator = ^value: Any => True :: {
    /* Validation logic that throws on failure */
    true
};
```

### 24.8.4 Avoid Boolean Blindness

```walnut
/* Avoid: Boolean result loses information */
/* checkPassword = ^password: String => Boolean */

/* Good: Use Result type */
checkPassword = ^password: String => Result<Null, String> :: {
    ?when(password->length < 8) {
        @'Password too short'
    } ~ ?when(!password->matchesRegexp(RegExp('/[A-Z]/'))) {
        @'Password must contain uppercase'
    } ~ {
        null
    }
};
```

### 24.8.5 Combine Conditions Clearly

```walnut
/* Good: Clear combined conditions */
isValidUser = isLoggedIn && hasVerifiedEmail && !isBanned;

/* Good: Named intermediate conditions */
hasRequiredFields = name->length > 0 && email->length > 0;
hasValidAge = age >= 18 && age <= 120;
isValid = hasRequiredFields && hasValidAge;

/* Avoid: Complex nested conditions */
/* ?when(a && (b || (c && !d)) || (!a && e)) { ... } */
```

### 24.8.6 Use Boolean Methods

```walnut
/* Collections provide boolean methods */
isEmpty = ^arr: Array => Boolean :: arr->length == 0;
isNotEmpty = ^arr: Array => Boolean :: arr->length > 0;

hasItems = ^arr: Array => Boolean :: arr->asBoolean;

/* String checks */
isBlank = ^str: String => Boolean :: str->trim->length == 0;
```

### 24.8.7 Document Boolean Parameters

```walnut
/* Good: Clear parameter names */
sendEmail = ^to: String, subject: String, shouldNotify: Boolean => Null :: {
    /* ... */
};

/* Avoid: Ambiguous boolean parameter */
/* sendEmail = ^to: String, subject: String, flag: Boolean => Null */
```

## 24.9 Truth Tables

### 24.9.1 AND Truth Table

| A     | B     | A && B |
|-------|-------|--------|
| false | false | false  |
| false | true  | false  |
| true  | false | false  |
| true  | true  | true   |

### 24.9.2 OR Truth Table

| A     | B     | A \|\| B |
|-------|-------|----------|
| false | false | false    |
| false | true  | true     |
| true  | false | true     |
| true  | true  | true     |

### 24.9.3 XOR Truth Table

| A     | B     | A ^^ B |
|-------|-------|--------|
| false | false | false  |
| false | true  | true   |
| true  | false | true   |
| true  | true  | false  |

### 24.9.4 NOT Truth Table

| A     | !A    |
|-------|-------|
| false | true  |
| true  | false |

## Summary

Walnut's Boolean type provides:

- **Enumeration-based** - Boolean is `(true, false)` with True and False subtypes
- **Logical operators** - AND (&&), OR (||), XOR (^^), NOT (!)
- **Type refinement** - True and False as distinct subtypes
- **Pattern matching** - Full support in ?when, ?whenValueOf, ?whenTypeOf
- **Type-safe conversions** - Casting to/from Integer, Real, String
- **Truthiness** - asBoolean for converting any value to boolean

Key features:
- Two values: `true` and `false`
- Type-safe logical operations
- Integration with conditional expressions
- Consistent truthiness rules (0, empty → false, others → true)
- No implicit conversions in comparisons

Boolean operations follow standard boolean algebra laws and provide a solid foundation for conditional logic in Walnut programs.
