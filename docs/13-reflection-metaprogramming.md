# 15. Reflection and Metaprogramming

## Overview

Walnut provides first-class support for types as values, enabling powerful reflection and metaprogramming capabilities. Types can be inspected, manipulated, and used in computations at runtime, while maintaining compile-time type safety.

## 15.1 Type Values

### 15.1.1 Creating Type Values

Type values are created using the backtick `` ` `` operator or the ``...` syntax.

**Syntax:**
- `` `TypeExpression `` - Short form
- ``TypeExpression` - Long form
- `type[...]` - For tuple/record types

**Examples:**
```walnut
/* Basic types */
intType = `Integer;
stringType = `String;
boolType = `Boolean;

/* Long form */
intType2 = `Integer;

/* Refined types */
ageType = `Integer<0..150>;
emailType = `String<5..254>;

/* Collection types */
arrayType = `Array<String>;
mapType = `Map<Integer>;
setType = `Set<Real>;

/* Tuple types */
pairType = type[Integer, String];
tripleType = type[Real, Real, Real];

/* Record types */
userType = type[id: Integer, name: String];
pointType = type[x: Real, y: Real];

/* Function types */
funcType = `^Integer => String;
transformType = `^Array<Integer> => Array<String>;

/* Union and intersection */
unionType = `Integer|String;
intersectionType = `[a: Integer, ...] & [b: String, ...];

/* User-defined types */
MyType := #[value: Integer];
myTypeValue = `MyType;
```

### 15.1.2 Type of Type Values

Type values themselves have types using `Type<T>`.

**Examples:**
```walnut
intType: Type<Integer> = `Integer;
stringType: Type<String> = `String;
anyType: Type = `Any;

/* Refined type value */
ageType: Type<Integer<0..150>> = `Integer<0..150>;

/* Generic type value */
arrayType: Type<Array> = `Array<String>;

/* Function type value */
funcType: Type<Function> = `^Integer => String;
```

## 15.2 Meta-Types

### 15.2.1 What Are Meta-Types?

Meta-types are special type categories that can only be used within `Type<...>` expressions. They allow restricting type parameters to specific kinds of types.

**Available meta-types:**
- `Function` - any function type
- `Tuple` - any tuple type
- `Record` - any record type
- `Union` - any union type
- `Intersection` - any intersection type
- `Atom` - any atom type
- `Enumeration` - any enumeration type
- `EnumerationSubset` - any enumeration subset type
- `IntegerSubset` - any integer subset type
- `RealSubset` - any real subset type
- `StringSubset` - any string subset type
- `MutableValue` - any mutable type
- `Alias` - any alias type
- `Data` - any data type
- `Open` - any open type
- `Sealed` - any sealed type
- `Named` - any named type (Atom, Enumeration, Alias, Data, Open, or Sealed)

### 15.2.2 Using Meta-Types

Meta-types enable type-level constraints on function parameters.

**Examples:**
```walnut
/* Function that only accepts function types */
getFunctionInfo = ^ft: Type<Function> => [Type, Type] ::
    [ft->parameterType, ft->returnType];

/* Function that only accepts enumeration types */
getEnumValues = ^et: Type<Enumeration> => Array ::
    et->values;

/* Function that only accepts record types */
getRecordKeys = ^rt: Type<Record> => Array<String> ::
    rt->itemTypes->keys;

/* Function that only accepts named types */
getTypeName = ^nt: Type<Named> => String ::
    nt->typeName;
```

## 15.3 Type Reflection Methods

### 15.3.1 Universal Type Methods

These methods are available on all `Type<T>` values.

**`isSubtypeOf`** - Check subtype relationship
```walnut
isSubtypeOf: Type->isSubtypeOf(Type => Boolean)

/* Examples */
`Integer->isSubtypeOf(`Real);           /* true */
`Integer<1..10>->isSubtypeOf(`Integer); /* true */
`Array<Integer>->isSubtypeOf(`Array);   /* true */
```

### 15.3.2 Type-Specific Reflection Methods

Different types expose different reflection methods based on their structure.

#### Array, Map, Set Types

**Methods:**
- `itemType` - Element/value type
- `minLength` - Minimum length (or 0)
- `maxLength` - Maximum length (or `PlusInfinity`)

**Examples:**
```walnut
reflectArray = ^Type<Array> => [Type, Integer<0..>, Integer<0..>|PlusInfinity] ::
    [#->itemType, #->minLength, #->maxLength];

reflectMap = ^Type<Map> => [Type, Integer<0..>, Integer<0..>|PlusInfinity] ::
    [#->itemType, #->minLength, #->maxLength];

arrayType = `Array<String, 5..10>;
info = reflectArray(arrayType);
/* info = [`String, 5, 10] */
```

#### String Type

**Methods:**
- `minLength` - Minimum length (or 0)
- `maxLength` - Maximum length (or `PlusInfinity`)

**Example:**
```walnut
reflectString = ^Type<String> => [Integer<0..>, Integer<0..>|PlusInfinity] ::
    [#->minLength, #->maxLength];

stringType = `String<5..100>;
info = reflectString(stringType);
/* info = [5, 100] */
```

#### Integer and Real Types

**Methods:**
- `minValue` - Minimum value (or `MinusInfinity`)
- `maxValue` - Maximum value (or `PlusInfinity`)

**Examples:**
```walnut
reflectInteger = ^Type<Integer> => [Integer|MinusInfinity, Integer|PlusInfinity] ::
    [#->minValue, #->maxValue];

reflectReal = ^Type<Real> => [Real|MinusInfinity, Real|PlusInfinity] ::
    [#->minValue, #->maxValue];

intType = `Integer<1..100>;
intInfo = reflectInteger(intType);
/* intInfo = [1, 100] */

realType = `Real<0..>;
realInfo = reflectReal(realType);
/* realInfo = [0, PlusInfinity] */
```

#### Tuple Type

**Methods:**
- `itemTypes` - Array of types for each position
- `restType` - Type for remaining elements

**Example:**
```walnut
reflectTuple = ^Type<Tuple> => [Array<Type>, Type] ::
    [#->itemTypes, #->restType];

tupleType = type[Integer, String, ...Real];
info = reflectTuple(tupleType);
/* info = [[`Integer, `String], `Real] */
```

#### Record Type

**Methods:**
- `itemTypes` - Map of key names to types
- `restType` - Type for additional keys

**Example:**
```walnut
reflectRecord = ^Type<Record> => [Map<Type>, Type] ::
    [#->itemTypes, #->restType];

recordType = type[id: Integer, name: String, ...Any];
info = reflectRecord(recordType);
/* info = [[id: `Integer, name: `String], `Any] */
```

#### Function Type

**Methods:**
- `parameterType` - Parameter type
- `returnType` - Return type

**Example:**
```walnut
reflectFunction = ^Type<Function> => [Type, Type] ::
    [#->parameterType, #->returnType];

funcType = `^Integer => String;
info = reflectFunction(funcType);
/* info = [`Integer, `String] */
```

#### Mutable Type

**Methods:**
- `valueType` - Inner value type

**Example:**
```walnut
reflectMutable = ^Type<Mutable> => Type ::
    #->valueType;

mutType = `Mutable<Integer>;
inner = reflectMutable(mutType);
/* inner = `Integer */
```

#### Result Type

**Methods:**
- `returnType` - Success type
- `errorType` - Error type

**Example:**
```walnut
reflectResult = ^Type<Result> => [Type, Type] ::
    [#->returnType, #->errorType];

resultType = `Result<Integer, String>;
info = reflectResult(resultType);
/* info = [`Integer, `String] */
```

#### Atom Type

**Methods:**
- `typeName` - Name of the atom type

**Example:**
```walnut
reflectAtom = ^Type<Atom> => String ::
    #->typeName;

MyAtom := ();
atomType = `MyAtom;
name = reflectAtom(atomType);
/* name = 'MyAtom' */
```

#### Enumeration Type

**Methods:**
- `typeName` - Name of the enumeration
- `values` - Array of enumeration values

**Example:**
```walnut
reflectEnumeration = ^Type<Enumeration> => [String, Array] ::
    [#->typeName, #->values];

Suit := (Clubs, Diamonds, Hearts, Spades);
suitType = `Suit;
info = reflectEnumeration(suitType);
/* info = ['Suit', [Suit.Clubs, Suit.Diamonds, Suit.Hearts, Suit.Spades]] */
```

#### EnumerationSubset Type

**Methods:**
- `enumerationType` - Base enumeration type
- `values` - Array of subset values

**Example:**
```walnut
reflectEnumerationSubset = ^Type<EnumerationSubset> => [Type, Array] ::
    [#->enumerationType, #->values];

Suit := (Clubs, Diamonds, Hearts, Spades);
RedSuit = Suit[Diamonds, Hearts];
redType = `RedSuit;
info = reflectEnumerationSubset(redType);
/* info = [`Suit, [Suit.Diamonds, Suit.Hearts]] */
```

#### IntegerSubset, RealSubset, StringSubset Types

**Methods:**
- `values` - Array of allowed values
- `minValue`, `maxValue` - For Integer/Real subsets
- `minLength`, `maxLength` - For String subsets

**Examples:**
```walnut
reflectIntegerSubset = ^Type<IntegerSubset> => Array<Integer> ::
    #->values;

reflectRealSubset = ^Type<RealSubset> => Array<Real> ::
    #->values;

reflectStringSubset = ^Type<StringSubset> => Array<String> ::
    #->values;

intSubset = `Integer[1, 2, 3, 5, 8];
values = reflectIntegerSubset(intSubset);
/* values = [1, 2, 3, 5, 8] */
```

#### Alias Type

**Methods:**
- `typeName` - Name of the alias
- `aliasedType` - The aliased type

**Example:**
```walnut
reflectAlias = ^Type<Alias> => [String, Type] ::
    [#->typeName, #->aliasedType];

UserId = Integer<1..>;
userIdType = `UserId;
info = reflectAlias(userIdType);
/* info = ['UserId', `Integer<1..>] */
```

#### Open Type

**Methods:**
- `typeName` - Name of the open type
- `valueType` - Underlying type

**Example:**
```walnut
reflectOpen = ^Type<Open> => [String, Type] ::
    [#->typeName, #->valueType];

ProductId := #Integer<1..>;
productIdType = `ProductId;
info = reflectOpen(productIdType);
/* info = ['ProductId', `Integer<1..>] */
```

#### Sealed Type

**Methods:**
- `typeName` - Name of the sealed type
- `valueType` - Underlying type (only accessible via reflection)

**Example:**
```walnut
reflectSealed = ^Type<Sealed> => [String, Type] ::
    [#->typeName, #->valueType];

UserId := $Integer<1..>;
userIdType = `UserId;
info = reflectSealed(userIdType);
/* info = ['UserId', `Integer<1..>] */
```

#### Union Type

**Methods:**
- `itemTypes` - Array of constituent types

**Example:**
```walnut
reflectUnion = ^Type<Union> => Array<Type> ::
    #->itemTypes;

unionType = `Integer|String|Boolean;
types = reflectUnion(unionType);
/* types = [`Integer, `String, `Boolean] */
```

#### Intersection Type

**Methods:**
- `itemTypes` - Array of constituent types

**Example:**
```walnut
reflectIntersection = ^Type<Intersection> => Array<Type> ::
    #->itemTypes;

intersectionType = `[a: Integer, ...] & [b: String, ...];
types = reflectIntersection(intersectionType);
/* types = [`[a: Integer, ...], `[b: String, ...]] */
```

#### Type Type

**Methods:**
- `refType` - The referenced type

**Example:**
```walnut
reflectType = ^Type<Type> => Type ::
    #->refType;

typeType = `Type<Integer>;
ref = reflectType(typeType);
/* ref = `Integer */
```

## 15.4 Runtime Type Inspection

### 15.4.1 Getting the Type of a Value

Every value has a `->type` method that returns its type.

**Examples:**
```walnut
x = 42;
xType = x->type;
/* xType = `Integer[42] */

str = 'hello';
strType = str->type;
/* strType = `String['hello'] */

arr = [1, 'two', 3.14];
arrType = arr->type;
/* arrType = type[Integer[1], String['two'], Real[3.14]] */
```

### 15.4.2 Type Checking

Use `->isOfType` to check if a value is of a given type.

**Syntax:** `value->isOfType(typeValue)`

**Examples:**
```walnut
checkType = ^value: Any, expectedType: Type => Boolean ::
    value->isOfType(expectedType);

x = 42;
checkType(x, `Integer);     /* true */
checkType(x, `String);      /* false */
checkType(x, `Real);        /* true (Integer <: Real) */
checkType(x, `Integer<1..100>);  /* true */
```

### 15.4.3 Dynamic Type Casting

Use `->as` to cast a value to a different type.

**Syntax:** `value->as(typeValue)`

**Examples:**
```walnut
castTo = ^value: Any, targetType: Type<T> => Result<T, String> ::
    ?when(value->isOfType(targetType)) {
        value->as(targetType)
    } ~ {
        @'Type mismatch'
    };

x: Any = 42;
result = castTo(x, `Integer);
/* result = 42 (type Integer) */
```

## 15.5 Practical Metaprogramming Examples

### 15.5.1 Generic Type Inspection

```walnut
/* Inspect tuple values based on their type */
inspectTuple = ^v: Array => Any :: {
    t = v->type;
    ?whenTypeOf(t) is {
        `Type<Tuple>: {
            itemTypes = t->itemTypes;
            itemTypes->mapIndexValue(^[index: Integer, valueType: Type] => Any ::
                v->item(#index)
            )
        },
        ~: null
    }
};

tuple = [42, 'hello', 3.14];
values = inspectTuple(tuple);
/* values = [42, 'hello', 3.14] */
```

### 15.5.2 Generic Record Inspection

```walnut
/* Inspect record values based on their type */
inspectRecord = ^v: Map => Any :: {
    t = v->type;
    ?whenTypeOf(t) is {
        `Type<Record>: {
            itemTypes = t->itemTypes;
            itemTypes->mapKeyValue(^[key: String, valueType: Type] => Any ::
                v->item(#key)
            )
        },
        ~: null
    }
};

record = [id: 1, name: 'Alice', age: 30];
values = inspectRecord(record);
/* values = [id: 1, name: 'Alice', age: 30] */
```

### 15.5.3 Function Type Inspection

```walnut
/* Get function signature information */
getFunctionSignature = ^f: ^Any => Any => [Type, Type] :: {
    t = f->type;
    ?whenTypeOf(t) is {
        `Type<Function>: [t->parameterType, t->returnType],
        ~: [null, null]
    }
};

myFunc = ^x: Integer => String :: x->asString;
signature = getFunctionSignature(myFunc);
/* signature = [`Integer, `String] */
```

### 15.5.4 Type-Safe Serialization

```walnut
/* Serialize based on type information */
serialize = ^value: Any => String :: {
    t = value->type;
    ?whenTypeOf(t) is {
        `Type<Integer>: 'int:' + value->asString,
        `Type<String>: 'str:' + value,
        `Type<Array>: 'arr:[' + value->map(serialize)->combineAsString(',') + ']',
        `Type<Record>: 'rec:{' + /* serialize record */ + '}',
        ~: 'unknown'
    }
};
```

### 15.5.5 Generic Validation

```walnut
/* Validate values against type constraints */
validateValue = ^value: Any, constraint: Type => Result<Boolean, String> ::
    ?when(value->isOfType(constraint)) {
        true
    } ~ {
        @'Value does not satisfy type constraint'
    };

/* Check integer range */
age = 25;
validateValue(age, `Integer<0..150>);  /* true */

/* Check string length */
name = 'Alice';
validateValue(name, `String<3..20>);   /* true */
```

### 15.5.6 Type-Driven Code Generation

```walnut
/* Generate validator code based on type */
generateValidator = ^t: Type => String ::
    ?whenTypeOf(t) is {
        `Type<Integer>: {
            min = t->minValue;
            max = t->maxValue;
            'value >= ' + min->asString + ' && value <= ' + max->asString
        },
        `Type<String>: {
            minLen = t->minLength;
            maxLen = t->maxLength;
            'value->length >= ' + minLen->asString + ' && value->length <= ' + maxLen->asString
        },
        ~: 'true'
    };
```

## 15.6 Metaprogramming Patterns

### 15.6.1 Type-Level Functions

```walnut
/* Extract element type from Array type */
getElementType = ^t: Type<Array> => Type ::
    t->itemType;

/* Check if type is optional (union with Null) */
isOptional = ^t: Type => Boolean ::
    ?whenTypeOf(t) is {
        `Type<Union>: t->itemTypes->contains(`Null),
        ~: false
    };
```

### 15.6.2 Dynamic Dispatch

```walnut
/* Process value based on its runtime type */
processValue = ^value: Any => String ::
    ?whenTypeOf(value->type) is {
        `Type<Integer>: 'Processing integer: ' + value->asString,
        `Type<String>: 'Processing string: ' + value,
        `Type<Array>: 'Processing array of length: ' + value->length->asString,
        ~: 'Unknown type'
    };
```

### 15.6.3 Type Transformations

```walnut
/* Transform array type to set type */
arrayToSetType = ^t: Type<Array> => Type<Set> ::
    `Set<#->itemType, #->minLength..#->maxLength>;

/* Unwrap optional type */
unwrapOptional = ^t: Type => Type ::
    ?whenTypeOf(t) is {
        `Type<Union>: {
            types = t->itemTypes->without(`Null);
            ?when(types->length == 1) {
                types->item(0)
            } ~ {
                t  /* Can't unwrap */
            }
        },
        ~: t
    };
```

### 15.6.4 Reflection-Based Utilities

```walnut
/* Get all keys from a record type */
getRecordKeys = ^t: Type<Record> => Array<String> ::
    t->itemTypes->keys;

/* Get function parameter count */
getFunctionArity = ^t: Type<Function> => Integer ::
    paramType = t->parameterType;
    ?whenTypeOf(paramType) is {
        `Type<Tuple>: paramType->itemTypes->length,
        `Type<Null>: 0,
        ~: 1
    };
```

## 15.7 Limitations and Considerations

### 15.7.1 Type Erasure

While types are first-class values, some type information may be erased or simplified at runtime for performance reasons.

### 15.7.2 Compile-Time vs Runtime

Type checking is primarily a compile-time activity. Runtime type inspection is provided for metaprogramming but should be used judiciously.

### 15.7.3 Performance

Excessive use of runtime type inspection can impact performance. Prefer compile-time type checking when possible.

## 15.8 Best Practices

### 15.8.1 Use Meta-Types for Constraints

```walnut
/* Good: Constrain to specific type categories */
processEnum = ^et: Type<Enumeration> => Array ::
    et->values;

/* Avoid: Accept any type */
/* processEnum = ^et: Type => Array */
```

### 15.8.2 Combine with Pattern Matching

```walnut
/* Good: Use type inspection with pattern matching */
handleValue = ^value: Any => String ::
    ?whenTypeOf(value->type) is {
        `Type<Integer>: handleInteger(value),
        `Type<String>: handleString(value),
        ~: 'Unknown'
    };
```

### 15.8.3 Document Reflection Usage

```walnut
/* Good: Document why reflection is needed */
/* Generic serializer that handles any type structure */
serialize = ^value: Any => String ::
    /* Use reflection to traverse type structure */
    /* ... */
;
```

## Summary

Walnut's reflection and metaprogramming features provide:

- **Type values** - Types as first-class values
- **Meta-types** - Categories for constraining type parameters
- **Type reflection** - Inspect type structure at runtime
- **Runtime type checking** - Check values against types
- **Dynamic casting** - Convert values between types
- **Type transformations** - Build new types from existing ones
- **Generic programming** - Write code that works with any type
- **Compile-time safety** - Type-safe metaprogramming

These capabilities enable:
- Generic libraries and frameworks
- Dynamic validation and serialization
- Type-driven code generation
- Flexible APIs with type safety
- Advanced type manipulations

While powerful, these features should be used thoughtfully, preferring compile-time type checking when possible and using runtime reflection primarily for truly dynamic scenarios.
