# Mutable Values

## Overview

Walnut is an immutable-by-default language where all values are typically immutable. However, controlled mutability is provided through the `Mutable<T>` type, which represents a mutable reference to a value of type `T`.

Mutable values enable:
- State management in closures and functions
- Efficient modification of collections
- Controlled side effects in a functional context

**Key characteristics:**
- Mutable values are the **only** way to have mutable state in Walnut
- The inner type `T` is **invariant** (not covariant)
- Mutations happen in-place and return the mutable reference for chaining
- All other values in Walnut remain immutable

## Mutable Type Syntax

### Type Annotations

The `Mutable<T>` type represents a mutable reference to a value of type `T`.

**Syntax:**
```walnut
Mutable<T>
```

**Examples:**
```walnut
/* Basic mutable types - used in type annotations for parameters/returns */
/* counter: Mutable<Integer> */
/* state: Mutable<String> */
/* list: Mutable<Array<Integer>> */
/* config: Mutable<[host: String, port: Integer]> */

/* With type constraints */
/* boundedCounter: Mutable<Integer<0..100>> */
/* shortString: Mutable<String<..50>> */
/* fixedArray: Mutable<Array<Real, 5..5>> */

/* Untyped mutable (any value) */
/* anyMutable: Mutable  -- Equivalent to Mutable<Any> */
```

### MutableValue Meta-Type

`MutableValue` is a meta-type used in type parameter constraints to indicate that a type must be a mutable type, without specifying the inner type.

**Examples:**
```walnut
/* Type constraint using MutableValue */
processMutable = ^m: Type<MutableValue> => String ::
    'Mutable type with inner type: ' + {m->valueType->printed};

/* Usage */
intMutableType = `Mutable<Integer>;
result = processMutable(intMutableType);
```

## Creating Mutable Values

Mutable values are created using the `mutable{Type, initialValue}` expression.

### Syntax

```walnut
mutable{Type, initialValue}
```

**Parameters:**
- `Type` - The type of the value to be stored (must be a type expression)
- `initialValue` - The initial value (must be of type `Type`)

### Basic Examples

```walnut
/* Integer mutable */
counter = mutable{Integer, 0};
/* Type: Mutable<Integer> */

/* String mutable */
message = mutable{String, 'Hello'};
/* Type: Mutable<String> */

/* Array mutable */
numbers = mutable{Array<Integer>, [1, 2, 3]};
/* Type: Mutable<Array<Integer>> */

/* Record mutable */
user = mutable{[name: String, age: Integer], [name: 'Alice', age: 30]};
/* Type: Mutable<[name: String, age: Integer]> */

/* Set mutable */
tags = mutable{Set<String>, ['tag1'; 'tag2']};
/* Type: Mutable<Set<String>> */
```

### With Type Constraints

```walnut
/* Bounded integer */
percentage = mutable{Integer<0..100>, 50};
/* Type: Mutable<Integer<0..100>> */

/* Limited string length */
username = mutable{String<3..20>, 'alice'};
/* Type: Mutable<String<3..20>> */

/* Fixed-size array */
coords = mutable{Array<Real, 3..3>, [0.0, 0.0, 0.0]};
/* Type: Mutable<Array<Real, 3..3>> */
```

### Nested Mutable Values

Mutable values can contain other mutable values:

```walnut
/* Mutable containing another mutable */
a = mutable{Integer, 25};
d = mutable{Mutable<Integer>, a};
/* Type: Mutable<Mutable<Integer>> */

/* Accessing nested value */
d->value->value;  /* Returns: 25 */

/* Modifying nested mutable */
d->value->SET(100);
a->value;  /* Returns: 100 (both reference the same mutable) */
```

### Converting to Mutable

Arrays can be converted to mutable using the `asMutableOfType` cast method:

```walnut
/* Convert array to mutable */
immutableArray = [1, 0, -42];
mutableArray = immutableArray->asMutableOfType(`Array<Integer>);
/* Type: Mutable<Array<Integer>> */
```

## Accessing Mutable Values

### The `value` Property

Access the current value of a mutable reference using the `->value` property:

```walnut
counter = mutable{Integer, 42};
currentValue = counter->value;  /* Returns: 42 */

message = mutable{String, 'Hello'};
text = message->value;  /* Returns: 'Hello' */

list = mutable{Array<Integer>, [1, 2, 3]};
items = list->value;  /* Returns: [1, 2, 3] */
```

**Notes:**
- The `->value` property always returns the **current** value
- The returned value is a **snapshot** (immutable copy) of the current state
- Multiple calls to `->value` may return different values if mutations occurred

## Mutation Operations

All mutation operations modify the mutable value **in-place** and return the mutable reference itself, allowing for **method chaining**.

### SET - Replace Value

The `SET` method replaces the current value with a new value.

**Syntax:**
```walnut
mutableRef->SET(newValue)
```

**Returns:** The mutable reference (for chaining)

**Examples:**
```walnut
/* Basic SET */
counter = mutable{Integer, 25};
counter->SET(10);
counter->value;  /* Returns: 10 */

/* String SET */
message = mutable{String, 'Hello'};
message->SET('World');
message->value;  /* Returns: 'World' */

/* Array SET */
list = mutable{Array<Integer>, [1, 2, 3]};
list->SET([4, 5, 6]);
list->value;  /* Returns: [4, 5, 6] */

/* Chaining after SET */
result = counter->SET(100)->value;  /* Returns: 100 */
```

**Type constraints:**
The new value must satisfy the type constraint of the mutable:

```walnut
bounded = mutable{Integer<0..100>, 50};
bounded->SET(75);   /* OK */
bounded->SET(150);  /* Runtime error: value out of range */
```

### APPEND - Append to String

The `APPEND` method appends a string to a mutable string value.

**Syntax:**
```walnut
mutableString->APPEND(suffix)
```

**Returns:** The mutable reference (for chaining)

**Applicable to:** `Mutable<String>` only

**Examples:**
```walnut
/* Basic APPEND */
message = mutable{String, 'Hello'};
message->APPEND(' World');
message->value;  /* Returns: 'Hello World' */

/* Chaining APPEND calls */
greeting = mutable{String, 'Hi'};
greeting->APPEND(' there')->APPEND('!')->APPEND(' Welcome.');
greeting->value;  /* Returns: 'Hi there! Welcome.' */

/* Using APPEND in expressions */
output = mutable{String, '<html>'};
output->APPEND('<body>')
    ->APPEND('<h1>Title</h1>')
    ->APPEND('</body>')
    ->APPEND('</html>');
```

### PUSH - Add to End of Array

The `PUSH` method adds an element to the end of a mutable array.

**Syntax:**
```walnut
mutableArray->PUSH(element)
```

**Returns:** The mutable reference (for chaining)

**Applicable to:** `Mutable<Array<T>>` where there is **no maximum size constraint**

**Examples:**
```walnut
/* Basic PUSH */
numbers = mutable{Array<Integer>, [1, 3, 5]};
numbers->PUSH(7);
numbers->value;  /* Returns: [1, 3, 5, 7] */

/* Chaining PUSH calls */
list = mutable{Array<String>, ['a']};
list->PUSH('b')->PUSH('c')->PUSH('d');
list->value;  /* Returns: ['a', 'b', 'c', 'd'] */

/* PUSH with type checking */
typed = mutable{Array<Integer>, []};
typed->PUSH(42);     /* OK */
typed->PUSH('str');  /* Compile error: type mismatch */
```

**Restriction:**
PUSH is **not available** for arrays with a maximum size constraint:

```walnut
/* Cannot use PUSH with max size constraint */
fixed = mutable{Array<Integer, ..5>, [1, 2, 3]};
fixed->PUSH(4);  /* Compile error: PUSH not available */
```

### UNSHIFT - Add to Beginning of Array

The `UNSHIFT` method adds an element to the beginning of a mutable array.

**Syntax:**
```walnut
mutableArray->UNSHIFT(element)
```

**Returns:** The mutable reference (for chaining)

**Applicable to:** `Mutable<Array<T>>` where there is **no maximum size constraint**

**Examples:**
```walnut
/* Basic UNSHIFT */
numbers = mutable{Array<Integer>, [1, 3, 5]};
numbers->UNSHIFT(9);
numbers->value;  /* Returns: [9, 1, 3, 5] */

/* Chaining UNSHIFT calls */
list = mutable{Array<String>, ['d']};
list->UNSHIFT('c')->UNSHIFT('b')->UNSHIFT('a');
list->value;  /* Returns: ['a', 'b', 'c', 'd'] */

/* Combined with PUSH */
items = mutable{Array<Integer>, [5]};
items->UNSHIFT(1)->PUSH(9);
items->value;  /* Returns: [1, 5, 9] */
```

### POP - Remove from End of Array

The `POP` method removes and returns the last element of a mutable array.

**Syntax:**
```walnut
mutableArray->POP
```

**Returns:** `Result<T, ItemNotFound>` where `T` is the array element type

**Applicable to:** `Mutable<Array<T>>` only

**Examples:**
```walnut
/* Basic POP */
numbers = mutable{Array<Integer>, [1, 3, 5]};
lastItem = numbers->POP;
/* lastItem: 5 (Result type) */
numbers->value;  /* Returns: [1, 3] */

/* POP from empty array */
empty = mutable{Array<Integer>, []};
result = empty->POP;
/* result: @ItemNotFound (error) */

/* Using POP with error handling */
pop = ^m: Mutable<Array<Integer>> => Result<Integer, ItemNotFound> ::
    m->POP;

list = mutable{Array<Integer>, [10, 20, 30]};
value = pop(list);  /* Returns: 30 */
```

**Note:**
- POP returns a `Result` type because it can fail if the array is empty
- On success, returns the removed element
- On failure (empty array), returns an `ItemNotFound` error

### SHIFT - Remove from Beginning of Array

The `SHIFT` method removes and returns the first element of a mutable array.

**Syntax:**
```walnut
mutableArray->SHIFT
```

**Returns:** `Result<T, ItemNotFound>` where `T` is the array element type

**Applicable to:** `Mutable<Array<T>>` only

**Examples:**
```walnut
/* Basic SHIFT */
numbers = mutable{Array<Integer>, [1, 3, 25]};
firstItem = numbers->SHIFT;
/* firstItem: 1 (Result type) */
numbers->value;  /* Returns: [3, 25] */

/* SHIFT from empty array */
empty = mutable{Array<Integer>, []};
result = empty->SHIFT;
/* result: @ItemNotFound (error) */

/* Using SHIFT with error handling */
shift = ^m: Mutable<Array<Integer>> => Result<Integer, ItemNotFound> ::
    m->SHIFT;

list = mutable{Array<Integer>, [1, 3, 5]};
value = shift(list);  /* Returns: 1 */
```

### ADD - Add to Set

The `ADD` method adds an element to a mutable set.

**Syntax:**
```walnut
mutableSet->ADD(element)
```

**Returns:** The mutable reference (for chaining)

**Applicable to:** `Mutable<Set<T>>` only

**Examples:**
```walnut
/* Basic ADD */
tags = mutable{Set<Integer>, [2; 5]};
tags->ADD(7);
tags->value;  /* Returns: [2; 5; 7] */

/* Adding duplicate (no effect on set) */
tags->ADD(7);
tags->value;  /* Still: [2; 5; 7] */

/* Chaining ADD calls */
numbers = mutable{Set<Integer>, [;]};
numbers->ADD(1)->ADD(2)->ADD(3)->ADD(2);
numbers->value;  /* Returns: [1; 2; 3] */

/* ADD with type checking */
stringSet = mutable{Set<String>, ['hello';]};
stringSet->ADD('world');  /* OK */
stringSet->ADD(42);       /* Compile error: type mismatch */
```

**Note:**
Sets automatically handle duplicates - adding an existing element has no effect.

### REMOVE - Remove from Set

The `REMOVE` method removes an element from a mutable set.

**Syntax:**
```walnut
mutableSet->REMOVE(element)
```

**Returns:** `Boolean` - `true` if element was removed, `false` if it didn't exist

**Applicable to:** `Mutable<Set<T>>` only

**Examples:**
```walnut
/* Basic REMOVE */
tags = mutable{Set<Integer>, [2; 5; 7]};
wasRemoved = tags->REMOVE(7);
/* wasRemoved: true */
tags->value;  /* Returns: [2; 5] */

/* Removing non-existent element */
wasRemoved = tags->REMOVE(7);
/* wasRemoved: false */
tags->value;  /* Still: [2; 5] */

/* Multiple REMOVE operations */
numbers = mutable{Set<Integer>, [1; 2; 3; 4; 5]};
numbers->REMOVE(2);
numbers->REMOVE(4);
numbers->value;  /* Returns: [1; 3; 5] */
```

### CLEAR - Clear Set

The `CLEAR` method removes all elements from a mutable set.

**Syntax:**
```walnut
mutableSet->CLEAR
```

**Returns:** The mutable reference (for chaining)

**Applicable to:** `Mutable<Set<T>>` only

**Examples:**
```walnut
/* Basic CLEAR */
tags = mutable{Set<Integer>, [2; 5; 7]};
tags->CLEAR;
tags->value;  /* Returns: [;] (empty set) */

/* Chaining after CLEAR */
numbers = mutable{Set<Integer>, [1; 2; 3]};
numbers->CLEAR->ADD(10)->ADD(20);
numbers->value;  /* Returns: [10; 20] */
```

### Filter - Filter Array Elements In-Place

The `Filter` method removes elements from a mutable array/set/map that don't match a predicate, modifying it in-place.

**Syntax:**
```walnut
mutableCollection->Filter(predicate)
```

**Returns:** The mutable reference (for chaining)

**Applicable to:** `Mutable<Array<T>>`, `Mutable<Map<T>>`, `Mutable<Set<T>>`

**Examples:**
```walnut
/* Filter array - keep only even numbers */
numbers = mutable{Array<Integer>, [1, 2, 3, 4, 5, 6]};
numbers->Filter(^# % 2 == 0);
numbers->value;  /* Returns: [2, 4, 6] */

/* Filter with complex condition */
users = mutable{Array<[name: String, age: Integer]>, [
    [name: 'Alice', age: 30],
    [name: 'Bob', age: 25],
    [name: 'Charlie', age: 35]
]};
users->Filter(^#.age >= 30);
users->value;  /* Returns: [[name: 'Alice', age: 30], [name: 'Charlie', age: 35]] */

/* Chaining Filter operations */
data = mutable{Array<Integer>, [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]};
data->Filter(^# > 3)->Filter(^# % 2 == 0);
data->value;  /* Returns: [4, 6, 8, 10] */

/* Filter set */
tags = mutable{Set<String>, ['apple'; 'banana'; 'apricot'; 'cherry']};
tags->Filter(^#->startsWith('a'));
tags->value;  /* Returns: ['apple'; 'apricot'] */
```

### Map - Transform Array Elements In-Place

The `Map` method transforms each element of a mutable array/set/map using a function, modifying it in-place.

**Syntax:**
```walnut
mutableCollection->Map(transform)
```

**Returns:** The mutable reference (for chaining)

**Applicable to:** `Mutable<Array<T>>`, `Mutable<Map<T>>`, `Mutable<Set<T>>`

**Note:** The transformation function must return a value of the same type as the collection's item type.

**Examples:**
```walnut
/* Map array - double all numbers */
numbers = mutable{Array<Integer>, [1, 2, 3, 4, 5]};
numbers->Map(^# * 2);
numbers->value;  /* Returns: [2, 4, 6, 8, 10] */

/* Map with complex transformation */
values = mutable{Array<Integer>, [1, 2, 3, 4, 5]};
values->Map(^?when(# % 2 == 0) { # * 10 } ~ { # });
values->value;  /* Returns: [1, 20, 3, 40, 5] */

/* Chaining Map with Filter */
data = mutable{Array<Integer>, [1, 2, 3, 4, 5]};
data->Filter(^# > 2)->Map(^# * 2);
data->value;  /* Returns: [6, 8, 10] */

/* Map set */
numbers = mutable{Set<Integer>, [1; 2; 3]};
numbers->Map(^# * 10);
numbers->value;  /* Returns: [10; 20; 30] */
```

### Sort - Sort Array In-Place

The `Sort` method sorts a mutable array in ascending order, modifying it in-place.

**Syntax:**
```walnut
mutableArray->Sort
```

**Returns:** The mutable reference (for chaining)

**Applicable to:** `Mutable<Array<T>>` where T is comparable (Integer, Real, or String)

**Examples:**
```walnut
/* Sort integers */
numbers = mutable{Array<Integer>, [3, 1, 4, 1, 5, 9, 2, 6]};
numbers->Sort;
numbers->value;  /* Returns: [1, 1, 2, 3, 4, 5, 6, 9] */

/* Sort reals */
values = mutable{Array<Real>, [3.14, 2.71, 1.41, 2.23]};
values->Sort;
values->value;  /* Returns: [1.41, 2.23, 2.71, 3.14] */

/* Sort strings */
names = mutable{Array<String>, ['charlie', 'alice', 'bob', 'david']};
names->Sort;
names->value;  /* Returns: ['alice', 'bob', 'charlie', 'david'] */

/* Chaining Sort with other operations */
data = mutable{Array<Integer>, [5, 2, 8, 1, 9]};
data->Filter(^# > 2)->Sort;
data->value;  /* Returns: [5, 8, 9] */
```

### Reverse - Reverse Array In-Place

The `Reverse` method reverses the order of elements in a mutable array, modifying it in-place.

**Syntax:**
```walnut
mutableArray->Reverse
```

**Returns:** The mutable reference (for chaining)

**Applicable to:** `Mutable<Array<T>>`

**Examples:**
```walnut
/* Reverse integers */
numbers = mutable{Array<Integer>, [1, 2, 3, 4, 5]};
numbers->Reverse;
numbers->value;  /* Returns: [5, 4, 3, 2, 1] */

/* Reverse strings */
words = mutable{Array<String>, ['hello', 'world', 'walnut']};
words->Reverse;
words->value;  /* Returns: ['walnut', 'world', 'hello'] */

/* Chaining Reverse with Sort */
data = mutable{Array<Integer>, [3, 1, 4, 1, 5, 9]};
data->Sort->Reverse;
data->value;  /* Returns: [9, 5, 4, 3, 1, 1] (descending order) */

/* Double reverse returns to original */
arr = mutable{Array<Integer>, [1, 2, 3]};
arr->Reverse->Reverse;
arr->value;  /* Returns: [1, 2, 3] */
```

### Shuffle - Shuffle Array In-Place

The `Shuffle` method randomly shuffles the elements of a mutable array, modifying it in-place.

**Syntax:**
```walnut
mutableArray->Shuffle
```

**Returns:** The mutable reference (for chaining)

**Applicable to:** `Mutable<Array<T>>`

**Examples:**
```walnut
/* Shuffle integers */
numbers = mutable{Array<Integer>, [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]};
numbers->Shuffle;
numbers->value;  /* Returns: [7, 2, 9, 1, 5, 3, 8, 6, 10, 4] (random order) */

/* Shuffle deck of cards */
deck = mutable{Array<String>, ['A♠', 'K♠', 'Q♠', 'J♠', '10♠']};
deck->Shuffle;
deck->value;  /* Returns: random permutation of the deck */

/* Shuffle before taking samples */
data = mutable{Array<Integer>, [1, 2, 3, 4, 5, 6, 7, 8, 9, 10]};
data->Shuffle;
sample = data->value->slice(0, 3);  /* Take first 3 after shuffle */

/* Chaining Shuffle with other operations */
arr = mutable{Array<Integer>, [1, 2, 3, 4, 5]};
arr->Filter(^# > 2)->Shuffle;
arr->value;  /* Returns: random permutation of [3, 4, 5] */
```

## Type Invariance

The inner type `T` in `Mutable<T>` is **invariant**, meaning:

- `Mutable<A>` is **not** a subtype of `Mutable<B>`, even if `A` is a subtype of `B`
- This prevents type safety violations

### Why Invariance?

```walnut
/* If Mutable were covariant (which it's NOT): */

/* Assume this were allowed (it's not): */
mutInt = mutable{Integer, 5};  /* Type: Mutable<Integer> */
mutReal = mutInt;  /* ERROR: Not allowed! Type mismatch: cannot assign Mutable<Integer> to Mutable<Real> */

/* Why? If this were allowed: */
mutReal->SET(3.14);  /* Would write a Real (3.14) into an Integer mutable */
mutInt->value;       /* Would return 3.14, violating type safety! */
```

**The invariance rule:**
- You **cannot** assign `Mutable<Integer>` to `Mutable<Real>`
- You **cannot** assign `Mutable<Real>` to `Mutable<Integer>`
- Each `Mutable<T>` type is distinct and incompatible

### Practical Implications

```walnut
/* These are incompatible types */
m1 = mutable{Integer, 5};
m2 = mutable{Real, 3.14};

m2->SET(m1->value);  /* Compile error: type mismatch */
m1->SET(m2->value);  /* Compile error: type mismatch */

/* Even with subtype relationships */
m3 = mutable{Integer<1..10>, 5};
m4 = m3;  /* Compile error: invariance - cannot assign Mutable<Integer<1..10>> to Mutable<Integer> */

/* Must use exact type match */
processMutable = ^m: Mutable<Integer> => Integer ::
    m->value;

processMutable(m1);  /* OK: exact type match */
processMutable(m3);  /* Compile error: type mismatch */
```

## Mutable Values in Closures

Mutable values are commonly used in closures to maintain state across function calls:

### Counter Example

```walnut
createCounter = ^initial: Integer => [increment: ^=> Integer, get: ^=> Integer] :: {
    count = mutable{Integer, initial};
    [
        increment: ^=> Integer :: {
            count->SET({count->value} + 1);
            count->value
        },
        get: ^=> Integer :: count->value
    ]
};

/* Usage */
counter = createCounter(0);
counter.increment();  /* Returns: 1 */
counter.increment();  /* Returns: 2 */
counter.get();        /* Returns: 2 */
```

### Accumulator Example

```walnut
makeAccumulator = ^=> ^Integer => Array<Integer> :: {
    values = mutable{Array<Integer>, []};
    ^n: Integer => Array<Integer> :: {
        values->PUSH(n);
        values->value
    }
};

/* Usage */
accumulate = makeAccumulator();
accumulate(10);  /* Returns: [10] */
accumulate(20);  /* Returns: [10, 20] */
accumulate(30);  /* Returns: [10, 20, 30] */
```

### State Machine Example

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

## Method Chaining

All mutation operations (except POP and SHIFT which return results, and REMOVE which returns boolean) return the mutable reference, enabling fluent method chaining:

### Array Chaining

```walnut
list = mutable{Array<Integer>, [5]};
list->PUSH(7)->UNSHIFT(1)->PUSH(9)->UNSHIFT(0);
list->value;  /* Returns: [0, 1, 5, 7, 9] */
```

### String Chaining

```walnut
html = mutable{String, ''};
html->APPEND('<html>')
    ->APPEND('<head><title>Page</title></head>')
    ->APPEND('<body>')
    ->APPEND('<h1>Welcome</h1>')
    ->APPEND('</body>')
    ->APPEND('</html>');
```

### Set Chaining

```walnut
tags = mutable{Set<String>, [;]};
tags->ADD('walnut')->ADD('functional')->ADD('typed');
tags->REMOVE('typed')->ADD('static-typing');
tags->value;  /* Returns: ['walnut'; 'functional'; 'static-typing'] */
```

### Mixed Operations

```walnut
state = mutable{Array<Integer>, [1, 2, 3]};
state->SET([])
     ->PUSH(10)
     ->PUSH(20)
     ->PUSH(30);
state->value;  /* Returns: [10, 20, 30] */
```

## Complete Examples

### Example 1: Building a List

```walnut
/* Function to build a list of numbers */
buildList = ^count: Integer => Array<Integer> :: {
    list = mutable{Array<Integer>, []};
    0->upTo(count - 1)->map(^i: Integer => {
        list->PUSH(i * 2)
    });
    list->value
};

result = buildList(5);  /* Returns: [0, 2, 4, 6, 8] */
```

### Example 2: String Builder

```walnut
createStringBuilder = ^=> [append: ^String => Null, build: ^=> String] :: {
    buffer = mutable{String, ''};
    [
        append: ^text: String => Null :: {
            buffer->APPEND(text);
            null
        },
        build: ^=> String :: buffer->value
    ]
};

/* Usage */
sb = createStringBuilder();
sb.append('Hello');
sb.append(' ');
sb.append('World');
result = sb.build();  /* Returns: 'Hello World' */
```

### Example 3: Set Operations

```walnut
uniqueValues = ^items: Array<Integer> => Set<Integer> :: {
    unique = mutable{Set<Integer>, [;]};
    items->map(^item: Integer => {
        unique->ADD(item)
    });
    unique->value
};

numbers = [1, 2, 3, 2, 1, 4, 5, 3];
result = uniqueValues(numbers);  /* Returns: [1; 2; 3; 4; 5] */
```

### Example 4: Stack Implementation

```walnut
createStack = ^=> [
    push: ^Integer => Null,
    pop: ^=> Result<Integer, ItemNotFound>,
    peek: ^=> Result<Integer, ItemNotFound>,
    isEmpty: ^=> Boolean
] :: {
    items = mutable{Array<Integer>, []};
    [
        push: ^value: Integer => Null :: {
            items->PUSH(value);
            null
        },
        pop: ^=> Result<Integer, ItemNotFound> :: items->POP,
        peek: ^=> Result<Integer, ItemNotFound> :: {
            ?when(items->value->length > 0) {
                items->value->{0}
            } ~ {
                @'ItemNotFound'
            }
        },
        isEmpty: ^=> Boolean :: items->value->length == 0
    ]
};

/* Usage */
stack = createStack();
stack.push(10);
stack.push(20);
stack.push(30);
top = stack.pop();        /* Returns: 30 */
next = stack.peek();      /* Returns: 20 */
empty = stack.isEmpty();  /* Returns: false */
```

### Example 5: Mutable Record State

```walnut
createUserState = ^initialName: String => [
    getName: ^=> String,
    setName: ^String => Null,
    getAge: ^=> Integer,
    setAge: ^Integer => Null
] :: {
    user = mutable{[name: String, age: Integer], [name: initialName, age: 0]};
    [
        getName: ^=> String :: user->value.name,
        setName: ^name: String => Null :: {
            user->SET([name: name, age: user->value.age]);
            null
        },
        getAge: ^=> Integer :: user->value.age,
        setAge: ^age: Integer => Null :: {
            user->SET([name: user->value.name, age: age]);
            null
        }
    ]
};

/* Usage */
userState = createUserState('Alice');
userState.getName();      /* Returns: 'Alice' */
userState.setAge(30);
userState.getAge();       /* Returns: 30 */
```

## Mutable vs Immutable Collections

Understanding when to use mutable vs immutable collections:

### Immutable Collections (Default)

```walnut
/* Arrays are immutable by default */
numbers = [1, 2, 3];
newNumbers = numbers->appendWith(4);
/* numbers: [1, 2, 3] (unchanged) */
/* newNumbers: [1, 2, 3, 4] (new array) */

/* Functional operations return new values */
doubled = numbers->map(^n: Integer => Integer :: n * 2);
/* numbers: [1, 2, 3] (unchanged) */
/* doubled: [2, 4, 6] (new array) */
```

### Mutable Collections

```walnut
/* Mutable arrays modify in-place */
numbers = mutable{Array<Integer>, [1, 2, 3]};
numbers->PUSH(4);
/* numbers->value: [1, 2, 3, 4] (modified) */

/* No new array created, same reference */
before = numbers;
numbers->PUSH(5);
/* before->value: [1, 2, 3, 4, 5] (same mutable) */
```

### When to Use Each

**Use immutable (default):**
- Pure functions and transformations
- Data pipelines and processing
- When you need to preserve history
- For functional programming patterns
- When working with constant values

**Use mutable:**
- Building collections incrementally
- State management in closures
- Performance-critical loops
- When you need in-place modification
- For accumulating results

## Reflection on Mutable Types

Type values for mutable types support reflection:

```walnut
/* Get the inner type of a mutable */
getMutableValueType = ^t: Type<MutableValue> => Type ::
    t->valueType;

mutType = `Mutable<Integer<1..100>>;
innerType = getMutableValueType(mutType);
/* Returns: `Integer<1..100> */

/* Check if a type is mutable */
isMutable = ^t: Type => Boolean ::
    t->isSubtypeOf(`Mutable);

isMutable(`Mutable<String>);  /* Returns: true */
isMutable(`String);           /* Returns: false */
```

## Best Practices

### 1. Use Mutable Sparingly

```walnut
/* Good - mutable for state management */
createCounter = ^=> ^=> Integer :: {
    count = mutable{Integer, 0};
    ^=> Integer :: {
        count->SET({count->value} + 1);
        count->value
    }
};

/* Avoid - unnecessary mutation */
addOne = ^x: Integer => Integer :: {
    m = mutable{Integer, x};
    m->SET({m->value} + 1);
    m->value  /* Just return x + 1 instead! */
};
```

### 2. Keep Mutables Encapsulated

```walnut
/* Good - mutable is internal */
createList = ^=> [add: ^Integer => Null, getAll: ^=> Array<Integer>] :: {
    items = mutable{Array<Integer>, []};
    [
        add: ^n: Integer => Null :: { items->PUSH(n); null },
        getAll: ^=> Array<Integer> :: items->value
    ]
};

/* Avoid - exposing mutable directly */
createList = ^=> Mutable<Array<Integer>> ::
    mutable{Array<Integer>, []};  /* Callers can mutate directly */
```

### 3. Use Type Constraints

```walnut
/* Good - constrained types prevent errors */
createPercentage = ^initial: Integer<0..100> => Mutable<Integer<0..100>> ::
    mutable{Integer<0..100>, initial};

/* Less safe - no constraint */
createPercentage = ^initial: Integer => Mutable<Integer> ::
    mutable{Integer, initial};  /* Could be set to invalid values */
```

### 4. Leverage Method Chaining

```walnut
/* Good - fluent chaining */
output = mutable{String, ''};
output->APPEND('<div>')
      ->APPEND('content')
      ->APPEND('</div>');

/* Verbose - separate statements */
output = mutable{String, ''};
output->APPEND('<div>');
output->APPEND('content');
output->APPEND('</div>');
```

### 5. Return Immutable Snapshots

```walnut
/* Good - return immutable copy */
getState = ^m: Mutable<Array<Integer>> => Array<Integer> ::
    m->value;  /* Returns snapshot */

/* Risky - return mutable reference */
getState = ^m: Mutable<Array<Integer>> => Mutable<Array<Integer>> ::
    m;  /* Callers can mutate */
```

## Summary

Mutable values in Walnut provide:

- **Controlled mutability** in an immutable-by-default language
- **Type-safe mutations** with compile-time checks
- **Rich mutation operations** for arrays, sets, and strings
- **Invariant type semantics** to prevent type safety violations
- **Method chaining** for fluent APIs
- **State management** in functional contexts through closures

Key operations summary:

| Operation | Applicable To | Returns | Description |
|-----------|---------------|---------|-------------|
| `SET` | All mutable types | Mutable ref | Replace value |
| `APPEND` | `Mutable<String>` | Mutable ref | Append to string |
| `PUSH` | `Mutable<Array>` (no max size) | Mutable ref | Add to end |
| `UNSHIFT` | `Mutable<Array>` (no max size) | Mutable ref | Add to beginning |
| `POP` | `Mutable<Array>` | Result | Remove from end |
| `SHIFT` | `Mutable<Array>` | Result | Remove from beginning |
| `ADD` | `Mutable<Set>` | Mutable ref | Add element |
| `REMOVE` | `Mutable<Set>` | Boolean | Remove element |
| `CLEAR` | `Mutable<Set>` | Mutable ref | Remove all elements |

Remember: Use mutable values judiciously, preferring immutable operations when possible, and leverage mutability for state management, accumulation, and performance-critical scenarios.
