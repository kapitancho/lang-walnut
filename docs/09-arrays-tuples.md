# 25. Arrays and Tuples

## Overview

Walnut provides two fundamental collection types for ordered sequences:

- **Array**: Dynamic, homogeneous collections with variable length
- **Tuple**: Fixed-size, heterogeneous collections with position-specific types

Both types are immutable by default, though mutable array operations are available through special methods.

## 25.1 Array Types

### 25.1.1 Basic Array Type

Arrays are homogeneous collections where all elements have the same type.

**Syntax:**
```walnut
Array<ElementType>
```

**Examples:**
```walnut
/* Basic arrays */
numbers = [1, 2, 3, 4, 5];              /* Type: Array<Integer> */
names = ['Alice', 'Bob', 'Charlie'];     /* Type: Array<String> */
flags = [true, false, true];             /* Type: Array<Boolean> */

/* Empty array */
empty = [];                              /* Type: Array<Any> */
```

### 25.1.2 Array Type Refinements

Arrays can have constraints on length and element types.

**Length constraints:**
```walnut
/* Fixed length */
RGB = Array<Integer, 3>;                 /* Exactly 3 elements */
coords = [10, 20, 30];                   /* Type: Array<Integer, 3> */

/* Fixed length shorthand - Array<n> is shorthand for Array<n..n> */
Triplet = Array<String, 3>;              /* Exactly 3 elements (also Array<3..3>) */
Pair = Array<Integer, 2>;                /* Exactly 2 elements (also Array<2..2>) */

/* Minimum length */
NonEmptyIntegers = Array<Integer, 1..>;  /* At least 1 element */
passwords = ['secret123'];               /* Type: Array<String, 1..> */

/* Range length */
SmallStringList = Array<String, 1..10>;  /* 1 to 10 elements */
```

**Element type refinements:**
```walnut
/* Arrays with refined element types */
Ages = Array<Integer<0..150>>;
Scores = Array<Real<0..100>>;
Emails = Array<String<5..254>>;

ages = [25, 30, 45];                     /* Type: Ages */
scores = [85.5, 92.0, 78.5];             /* Type: Scores */
```

### 25.1.3 Array Literals

**Syntax:**
```walnut
[element1, element2, element3]
[]                                       /* Empty array */
```

**Examples:**
```walnut
/* Integer array */
primes = [2, 3, 5, 7, 11, 13];

/* String array */
colors = ['red', 'green', 'blue'];

/* Nested arrays */
matrix = [[1, 2, 3], [4, 5, 6], [7, 8, 9]];
/* Type: Array<Array<Integer>> */

/* Array with trailing comma */
items = [
    'first',
    'second',
    'third',
];
```

## 25.2 Array Operations

### 25.2.1 Basic Properties

**`length` - Get array length**
```walnut
/* Signature */
^Array<T> => Integer

/* Examples */
[1, 2, 3]->length;                       /* 3 */
[]->length;                              /* 0 */
['a', 'b', 'c', 'd', 'e']->length;       /* 5 */
```

**`item(index)` - Access element by index**
```walnut
/* Signature */
^[Array<T>, Integer] => T

/* Examples */
colors = ['red', 'green', 'blue'];
colors->item(0);                         /* 'red' */
colors->item(1);                         /* 'green' */
colors->item(2);                         /* 'blue' */
colors->item(-1);                        /* 'blue' (last element) */
colors->item(-2);                        /* 'green' (second-to-last) */
```

**`reverse` - Reverse array order**
```walnut
/* Signature */
^Array<T> => Array<T>

/* Examples */
[1, 2, 3]->reverse;                      /* [3, 2, 1] */
['a', 'b', 'c']->reverse;                /* ['c', 'b', 'a'] */
```

### 25.2.2 Searching

**`contains(value)` - Check if array contains value**
```walnut
/* Signature */
^[Array<T>, T] => Boolean

/* Examples */
[1, 2, 3, 4, 5]->contains(3);            /* true */
['apple', 'banana']->contains('orange'); /* false */
```

**`first` - Get first element**
```walnut
/* Signature */
Array<T, min..max>->first(Null => T)                    /* When min > 0 */
Array<T, min..max>->first(Null => Result<T, ItemNotFound>) /* When min could be 0 */

/* Examples */
[1, 2, 3]->first;                        /* 1 */
['apple', 'banana', 'cherry']->first;    /* 'apple' */

/* Type-safe - no error possible when min > 0 */
getFirst = ^arr: Array<String, 1..> => String :: arr->first;
getFirst(['a', 'b', 'c']);               /* 'a' */

/* Result type when array might be empty */
getMaybeFirst = ^arr: Array<String> => Result<String, ItemNotFound> :: arr->first;
getMaybeFirst([]);                       /* @ItemNotFound */
getMaybeFirst(['x', 'y']);               /* 'x' */

/* Works with tuples */
tuple = [1, 'hello', true];
tuple->first;                            /* 1 : Integer */
```

The return type is automatically inferred:
- If the array's minimum length is greater than 0, returns `T` directly (no error possible)
- If the array's minimum length is 0, returns `Result<T, ItemNotFound>` (might be empty)

**`last` - Get last element**
```walnut
/* Signature */
Array<T, min..max>->last(Null => T)                    /* When min > 0 */
Array<T, min..max>->last(Null => Result<T, ItemNotFound>) /* When min could be 0 */

/* Examples */
[1, 2, 3]->last;                         /* 3 */
['apple', 'banana', 'cherry']->last;     /* 'cherry' */

/* Type-safe - no error possible when min > 0 */
getLast = ^arr: Array<String, 1..> => String :: arr->last;
getLast(['a', 'b', 'c']);                /* 'c' */

/* Result type when array might be empty */
getMaybeLast = ^arr: Array<String> => Result<String, ItemNotFound> :: arr->last;
getMaybeLast([]);                        /* @ItemNotFound */
getMaybeLast(['x', 'y']);                /* 'y' */

/* Works with tuples */
tuple = [1, 'hello', true];
tuple->last;                             /* true : Boolean */
```

The return type is automatically inferred:
- If the array's minimum length is greater than 0, returns `T` directly (no error possible)
- If the array's minimum length is 0, returns `Result<T, ItemNotFound>` (might be empty)

**`indexOf(value)` - Find first index of value**
```walnut
/* Signature */
^[Array<T>, T] => Integer | Null

/* Examples */
[10, 20, 30, 20]->indexOf(20);           /* 1 */
[10, 20, 30]->indexOf(40);               /* null */
```

**`lastIndexOf(value)` - Find last index of value**
```walnut
/* Signature */
^[Array<T>, T] => Integer | Null

/* Examples */
[10, 20, 30, 20]->lastIndexOf(20);       /* 3 */
[10, 20, 30]->lastIndexOf(40);           /* null */
```

**`findFirst(predicate)` - Find first matching element**
```walnut
/* Signature */
^[Array<T>, ^T => Boolean] => T | Null

/* Examples */
[1, 2, 3, 4, 5]->findFirst(^# > 3);      /* 4 */
[1, 2, 3]->findFirst(^# > 10);           /* null */

users = [
    [name: 'Alice', age: 30],
    [name: 'Bob', age: 25]
];
users->findFirst(^#.age >= 30);          /* [name: 'Alice', age: 30] */
```

**`findLast(predicate)` - Find last matching element**
```walnut
/* Signature */
^[Array<T>, ^T => Boolean] => T | Null

/* Examples */
[1, 2, 3, 4, 5]->findLast(^# > 3);       /* 5 */
[1, 2, 3]->findLast(^# > 10);            /* null */
```

### 25.2.3 Adding Elements

**`append(value)` - Add element to end**
```walnut
/* Signature */
^[Array<T>, T] => Array<T>

/* Examples */
[1, 2, 3]->append(4);                    /* [1, 2, 3, 4] */
[]->append('first');                     /* ['first'] */

/* Chain multiple appends */
[1, 2]->append(3)->append(4)->append(5); /* [1, 2, 3, 4, 5] */
```

**`prepend(value)` - Add element to beginning**
```walnut
/* Signature */
^[Array<T>, T] => Array<T>

/* Examples */
[2, 3, 4]->prepend(1);                   /* [1, 2, 3, 4] */
[]->prepend('first');                    /* ['first'] */
```

**`insert(index, value)` - Insert element at index**
```walnut
/* Signature */
^[Array<T>, Integer, T] => Array<T>

/* Examples */
[1, 2, 4, 5]->insert(2, 3);              /* [1, 2, 3, 4, 5] */
['a', 'c']->insert(1, 'b');              /* ['a', 'b', 'c'] */
```

**`concat(other)` - Concatenate two arrays**
```walnut
/* Signature */
^[Array<T>, Array<T>] => Array<T>

/* Examples */
[1, 2, 3]->concat([4, 5, 6]);            /* [1, 2, 3, 4, 5, 6] */
['a', 'b']->concat(['c', 'd']);          /* ['a', 'b', 'c', 'd'] */

/* Concatenate multiple arrays */
[1, 2]->concat([3, 4])->concat([5, 6]);  /* [1, 2, 3, 4, 5, 6] */
```

### 25.2.4 Removing Elements

**`remove(index)` - Remove element at index**
```walnut
/* Signature */
^[Array<T>, Integer] => Array<T>

/* Examples */
[1, 2, 3, 4, 5]->remove(2);              /* [1, 2, 4, 5] */
['a', 'b', 'c']->remove(0);              /* ['b', 'c'] */
[10, 20, 30]->remove(-1);                /* [10, 20] (remove last) */
```

**`removeFirst(value)` - Remove first occurrence of value**
```walnut
/* Signature */
^[Array<T>, T] => Array<T>

/* Examples */
[1, 2, 3, 2, 1]->removeFirst(2);         /* [1, 3, 2, 1] */
['a', 'b', 'c']->removeFirst('b');       /* ['a', 'c'] */
```

**`removeAll(value)` - Remove all occurrences of value**
```walnut
/* Signature */
^[Array<T>, T] => Array<T>

/* Examples */
[1, 2, 3, 2, 1]->removeAll(2);           /* [1, 3, 1] */
[1, 1, 1, 2, 3]->removeAll(1);           /* [2, 3] */
```

### 25.2.5 Transformation

**`map(transform)` - Transform each element**
```walnut
/* Signature */
^[Array<T>, ^T => U] => Array<U>

/* Examples */
[1, 2, 3, 4, 5]->map(^# * 2);            /* [2, 4, 6, 8, 10] */
['hello', 'world']->map(^#->toUpperCase); /* ['HELLO', 'WORLD'] */

users = [[name: 'Alice'], [name: 'Bob']];
users->map(^#.name);                     /* ['Alice', 'Bob'] */
```

**`mapIndexValue(transform)` - Transform with index**
```walnut
/* Signature */
^[Array<T>, ^[Integer, T] => U] => Array<U>

/* Examples */
['a', 'b', 'c']->mapIndexValue(^[#index, #value] ::
    #value + (#index + 1)->asString
);                                       /* ['a1', 'b2', 'c3'] */
```

**`filter(predicate)` - Keep matching elements**
```walnut
/* Signature */
^[Array<T>, ^T => Boolean] => Array<T>

/* Examples */
[1, 2, 3, 4, 5]->filter(^# > 3);         /* [4, 5] */
[-2, -1, 0, 1, 2]->filter(^# >= 0);      /* [0, 1, 2] */

users = [
    [name: 'Alice', age: 30],
    [name: 'Bob', age: 25],
    [name: 'Charlie', age: 35]
];
users->filter(^#.age >= 30);             /* [[name: 'Alice', age: 30],
                                            [name: 'Charlie', age: 35]] */
```

**`reduce([reducer, initial])` - Reduce to single value**
```walnut
/* Signature */
^[Array<T>, [reducer: ^[result: A, item: T] => A, initial: A]] => A

/* Examples */
/* Sum all numbers */
[1, 2, 3, 4, 5]->reduce[
    reducer: ^[result: Integer, item: Integer] => Integer :: #result + #item,
    initial: 0
];  /* 15 */

/* Concatenate strings */
['hello', ' ', 'world']->reduce[
    reducer: ^[result: String, item: String] => String :: #result + #item,
    initial: ''
];  /* 'hello world' */

/* Transform types - build comma-separated string from integers */
[1, 2, 3, 4, 5]->reduce[
    reducer: ^[result: String, item: Integer] => String ::
        ?when(#result == '') { #item->asString }
        ~ { #result + ', ' + #item->asString },
    initial: ''
];  /* '1, 2, 3, 4, 5' */

/* Complex aggregation - count occurrences */
[1, 2, 2, 3, 2, 4]->reduce[
    reducer: ^[result: [count: Integer], item: Integer] => [count: Integer] ::
        ?when(#item == 2) {
            [count: #result.count + 1]
        } ~ {
            #result
        },
    initial: [count: 0]
];  /* [count: 3] */
```

**`sort(options)` - Sort array in ascending or descending order**
```walnut
/* Signature */
^[Array<T>, Null|[reverse: Boolean]] => Array<T>  /* where T is comparable */

/* Examples */
[3, 1, 4, 1, 5, 9]->sort(null);                /* [1, 1, 3, 4, 5, 9] */
['charlie', 'alice', 'bob']->sort(null);       /* ['alice', 'bob', 'charlie'] */

/* Sort in descending order */
[3, 1, 4, 1, 5, 9]->sort([reverse: true]);     /* [9, 5, 4, 3, 1, 1] */
['charlie', 'alice', 'bob']->sort([reverse: true]);  /* ['charlie', 'bob', 'alice'] */

/* Sort in ascending order (explicit) */
[3, 1, 4, 1, 5, 9]->sort([reverse: false]);    /* [1, 1, 3, 4, 5, 9] */
```

**`sortBy(keyExtractor)` - Sort by custom key**
```walnut
/* Signature */
^[Array<T>, ^T => U] => Array<T>  /* where U is comparable */

/* Examples */
users = [
    [name: 'Charlie', age: 35],
    [name: 'Alice', age: 30],
    [name: 'Bob', age: 25]
];

/* Sort by age */
users->sortBy(^#.age);
/* [
    [name: 'Bob', age: 25],
    [name: 'Alice', age: 30],
    [name: 'Charlie', age: 35]
] */

/* Sort by name */
users->sortBy(^#.name);
/* [
    [name: 'Alice', age: 30],
    [name: 'Bob', age: 25],
    [name: 'Charlie', age: 35]
] */
```

**`unique()` - Remove duplicate elements**
```walnut
/* Signature */
^Array<T> => Array<T>

/* Examples */
[1, 2, 2, 3, 3, 3, 4]->unique();         /* [1, 2, 3, 4] */
['a', 'b', 'a', 'c', 'b']->unique();     /* ['a', 'b', 'c'] */
```

**`chunk(size)` - Split array into chunks of specified size**
```walnut
/* Signature */
^[Array<T, minL..maxL>, Integer<minS..maxS>] => Array<Array<T, minI..maxI>, minO..maxO>

/* Examples */
/* Basic chunking */
[1, 2, 3, 4, 5]->chunk(2);               /* [[1, 2], [3, 4], [5]] */

/* Exact division */
[1, 2, 3, 4, 5, 6]->chunk(3);            /* [[1, 2, 3], [4, 5, 6]] */

/* Chunk size of 1 */
[1, 2, 3]->chunk(1);                     /* [[1], [2], [3]] */

/* Chunk size larger than array */
[1, 2, 3]->chunk(10);                    /* [[1, 2, 3]] */

/* Empty array */
[]->chunk(2);                            /* [] */

/* Chunking strings */
['a', 'b', 'c', 'd', 'e']->chunk(2);     /* [['a', 'b'], ['c', 'd'], ['e']] */

/* Type inference with refined bounds */
chunkData = ^[arr: Array<Integer, 5>, size: Integer<2>] =>
    Array<Array<Integer, 1..2>, 3> ::
    arr->chunk(size);
```

**`flatten()` - Flatten nested array by one level**
```walnut
/* Signature */
^Array<Array<T>> => Array<T>

/* Examples */
[[1, 2], [3, 4], [5, 6]]->flatten();     /* [1, 2, 3, 4, 5, 6] */
[['a'], ['b', 'c'], ['d']]->flatten();   /* ['a', 'b', 'c', 'd'] */
```

**`flatMap(transform)` - Map and flatten in one operation**
```walnut
/* Signature */
^[Array<T, minL..maxL>, ^T => Array<U, minI..maxI>] => Array<U, minL*minI..maxL*maxI>

/* Examples */
/* Duplicate each element */
[1, 2, 3]->flatMap(^i: Integer => Array<Integer> :: [i, i]);
/* [1, 1, 2, 2, 3, 3] */

/* Expand nested data */
users = [
    [name: 'Alice', tags: ['dev', 'admin']],
    [name: 'Bob', tags: ['user']]
];
users->flatMap(^# => Array<String> :: #.tags);
/* ['dev', 'admin', 'user'] */

/* Conditional expansion */
[1, 2, 3, 4]->flatMap(^i: Integer => Array<Integer> ::
    match i % 2 == 0 {
        Boolean.true :: [i, i * 2],
        Boolean.false :: []
    }
);
/* [2, 4, 4, 8] - only even numbers, doubled */

/* Result type with error handling */
processWithErrors = ^[Array<String>, ^String => Result<Array<Integer>, Error>] =>
    Result<Array<Integer>, Error> ::
    #arr->flatMap(#fn);
/* Stops at first error, returns Result<Array<Integer>, Error> */

/* Type inference with bounds */
flatMapData = ^[arr: Array<Integer, 3>, fn: ^Integer => Array<String, 2>] =>
    Array<String, 6..6> ::
    arr->flatMap(fn);
/* Result is exactly 6 elements (3 * 2) */
```

**`partition(predicate)` - Split array into matching and non-matching elements**
```walnut
/* Signature */
^[Array<T>, ^T => Boolean] => [matching: Array<T>, notMatching: Array<T>]

/* Examples */
/* Split even and odd numbers */
[1, 2, 3, 4, 5, 6]->partition(^i: Integer => Boolean :: i % 2 == 0);
/* [matching: [2, 4, 6], notMatching: [1, 3, 5]] */

/* Separate active users */
users = [
    [name: 'Alice', active: true],
    [name: 'Bob', active: false],
    [name: 'Charlie', active: true]
];
result = users->partition(^#.active);
activeUsers = result.matching;          /* [[name: 'Alice', ...], [name: 'Charlie', ...]] */
inactiveUsers = result.notMatching;     /* [[name: 'Bob', ...]] */

/* Access parts directly */
[1, 2, 3, 4, 5]->partition(^# > 3).matching;    /* [4, 5] */
[1, 2, 3, 4, 5]->partition(^# > 3).notMatching; /* [1, 2, 3] */

/* Count each partition */
result = data->partition(^#.isValid);
validCount = result.matching->length;
invalidCount = result.notMatching->length;

/* Both partitions have same element type as original array */
partitionData = ^a: Array<String|Integer, 3..7> =>
    [matching: Array<String|Integer, ..7>, notMatching: Array<String|Integer, ..7>] ::
    a->partition(^v: Integer|String => Boolean :: v->isOfType(`String));
```

### 25.2.6 Slicing and Padding

**`slice(start, end)` - Extract subarray**
```walnut
/* Signature */
^[Array<T>, Integer, Integer] => Array<T>

/* Examples */
[0, 1, 2, 3, 4, 5]->slice(2, 5);         /* [2, 3, 4] */
['a', 'b', 'c', 'd']->slice(1, 3);       /* ['b', 'c'] */

/* Negative indices */
[0, 1, 2, 3, 4]->slice(-3, -1);          /* [2, 3] */
```

**`take(count)` - Take first n elements**
```walnut
/* Signature */
^[Array<T, minL..maxL>, Integer<0..>] => Array<T, minR..maxR>

/* Examples */
[1, 2, 3, 4, 5]->take(3);                /* [1, 2, 3] */
['a', 'b', 'c']->take(2);                /* ['a', 'b'] */
[1, 2]->take(5);                         /* [1, 2] (no error if too few) */
[]->take(3);                             /* [] */

/* Type inference */
takeThree = ^[arr: Array<String, 3..6>, n: Integer<2..8>] =>
    Array<String, 2..6> ::
    arr->take(n);
/* Result type is calculated: min(minL, minCount) to min(maxL, maxCount) */
```

**`drop(count)` - Skip first n elements**
```walnut
/* Signature */
^[Array<T, minL..maxL>, Integer<0..>] => Array<T, minR..maxR>

/* Examples */
[1, 2, 3, 4, 5]->drop(2);                /* [3, 4, 5] */
['a', 'b', 'c', 'd']->drop(1);           /* ['b', 'c', 'd'] */
[1, 2]->drop(5);                         /* [] (no error if too many) */

/* Type inference */
dropTwo = ^[arr: Array<String, 4..16>, n: Integer<1..3>] =>
    Array<String, 1..15> ::
    arr->drop(n);
/* Result type is calculated based on input constraints */
```

**`padLeft(length, value)` - Pad on the left**
```walnut
/* Signature */
^[Array<T>, Integer, T] => Array<T>

/* Examples */
[1, 2, 3]->padLeft(5, 0);                /* [0, 0, 1, 2, 3] */
['a']->padLeft(3, 'x');                  /* ['x', 'x', 'a'] */
[1, 2, 3, 4]->padLeft(3, 0);             /* [1, 2, 3, 4] (no change if already long enough) */
```

**`padRight(length, value)` - Pad on the right**
```walnut
/* Signature */
^[Array<T>, Integer, T] => Array<T>

/* Examples */
[1, 2, 3]->padRight(5, 0);               /* [1, 2, 3, 0, 0] */
['a']->padRight(3, 'x');                 /* ['a', 'x', 'x'] */
```

### 25.2.7 Aggregation

**`sum()` - Sum numeric elements**
```walnut
/* Signature */
^Array<Integer> => Integer
^Array<Real> => Real

/* Examples */
[1, 2, 3, 4, 5]->sum();                  /* 15 */
[10.5, 20.3, 15.2]->sum();               /* 46.0 */
[]->sum();                               /* 0 */
```

**`product()` - Multiply numeric elements**
```walnut
/* Signature */
^Array<Integer> => Integer
^Array<Real> => Real

/* Examples */
[2, 3, 4]->product();                    /* 24 */
[1.5, 2.0, 3.0]->product();              /* 9.0 */
[]->product();                           /* 1 */
```

**`min()` - Find minimum value**
```walnut
/* Signature */
^Array<T> => T | Null  /* where T is comparable */

/* Examples */
[3, 1, 4, 1, 5, 9]->min();               /* 1 */
['charlie', 'alice', 'bob']->min();      /* 'alice' */
[]->min();                               /* null */
```

**`max()` - Find maximum value**
```walnut
/* Signature */
^Array<T> => T | Null  /* where T is comparable */

/* Examples */
[3, 1, 4, 1, 5, 9]->max();               /* 9 */
['charlie', 'alice', 'bob']->max();      /* 'charlie' */
[]->max();                               /* null */
```

**`all(predicate)` - Check if all elements match**
```walnut
/* Signature */
^[Array<T>, ^T => Boolean] => Boolean

/* Examples */
[2, 4, 6, 8]->all(^# % 2 == 0);          /* true (all even) */
[1, 2, 3, 4]->all(^# > 0);               /* true (all positive) */
[1, 2, 3, 4]->all(^# > 2);               /* false */
[]->all(^# > 0);                         /* true (vacuously true) */
```

**`any(predicate)` - Check if any element matches**
```walnut
/* Signature */
^[Array<T>, ^T => Boolean] => Boolean

/* Examples */
[1, 3, 5, 8]->any(^# % 2 == 0);          /* true (has even number) */
[1, 3, 5, 7]->any(^# % 2 == 0);          /* false (all odd) */
[]->any(^# > 0);                         /* false */
```

**`count(predicate)` - Count matching elements**
```walnut
/* Signature */
^[Array<T>, ^T => Boolean] => Integer

/* Examples */
[1, 2, 3, 4, 5, 6]->count(^# % 2 == 0);  /* 3 (even numbers) */
['apple', 'apricot', 'banana']->count(
    ^#->startsWith('a')
);                                       /* 2 */
```

### 25.2.8 Conversion

**`join(separator)` - Join elements into string**
```walnut
/* Signature */
^[Array<T>, String] => String

/* Examples */
[1, 2, 3, 4, 5]->join(', ');             /* '1, 2, 3, 4, 5' */
['hello', 'world']->join(' ');           /* 'hello world' */
['a', 'b', 'c']->join('');               /* 'abc' */
[]->join(', ');                          /* '' */
```

**`indexBy(keyExtractor)` - Create map indexed by extracted key**
```walnut
/* Signature */
^[Array<T>, ^T => String] => Map<T>

/* Examples */
/* Index users by ID */
users = [
    [id: 1, name: 'Alice', email: 'alice@example.com'],
    [id: 2, name: 'Bob', email: 'bob@example.com'],
    [id: 3, name: 'Charlie', email: 'charlie@example.com']
];

userById = users->indexBy(^#.id->asString);
/* Map with keys '1', '2', '3' mapping to user records */

/* Index by email */
userByEmail = users->indexBy(^#.email);
/* Map with keys 'alice@example.com', etc. */

/* Access indexed data */
alice = userById->item('1');  /* [id: 1, name: 'Alice', email: 'alice@example.com'] */

/* Index products by category */
products = [
    [name: 'Apple', category: 'fruit'],
    [name: 'Carrot', category: 'vegetable'],
    [name: 'Banana', category: 'fruit']
];

/* Note: duplicate keys will keep the last occurrence */
byCategory = products->indexBy(^#.category);
/* Map with 'fruit' => [name: 'Banana', ...] (overwrites Apple) */
```

**`groupBy(keyExtractor)` - Group array elements by key into a Map of Arrays**
```walnut
/* Signature */
^[Array<T, minL..maxL>, ^T => String] => Map<Array<T, minG..maxL>, minM..maxL>

/* Examples */
/* Group numbers by parity */
[1, 2, 3, 4, 5, 6]->groupBy(^i: Integer => String ::
    match i % 2 == 0 {
        Boolean.true :: 'even',
        Boolean.false :: 'odd'
    }
);
/* [odd: [1, 3, 5], even: [2, 4, 6]] */

/* Group users by status */
users = [
    [name: 'Alice', status: 'active'],
    [name: 'Bob', status: 'inactive'],
    [name: 'Charlie', status: 'active'],
    [name: 'David', status: 'pending']
];

byStatus = users->groupBy(^#.status);
/* [
    active: [[name: 'Alice', ...], [name: 'Charlie', ...]],
    inactive: [[name: 'Bob', ...]],
    pending: [[name: 'David', ...]]
] */

/* Access groups */
activeUsers = byStatus->item('active');         /* Array of active users */
activeCount = byStatus->item('active')->length; /* Count active users */

/* Group by length */
words = ['ant', 'bear', 'cat', 'dog', 'elephant'];
byLength = words->groupBy(^s: String => String :: s->length->asString);
/* [
    3: ['ant', 'cat', 'dog'],
    4: ['bear'],
    8: ['elephant']
] */

/* Group by type */
mixedData = [1, 'hello', 2.5, 'world', 3];
byType = mixedData->groupBy(^v: Real|String => String ::
    ?whenTypeOf(v) {
        `String: 'string',
        `Integer: 'integer',
        `Real: 'real'
    }
);
/* [
    integer: [1, 3],
    string: ['hello', 'world'],
    real: [2.5]
] */

/* All groups contain at least 1 element */
groupedData = ^a: Array<String, 5..10> => Map<Array<String, 1..10>, 1..10> ::
    a->groupBy(^s: String => String :: s->length->asString);
/* Each group array has 1..10 elements, and there are 1..10 groups */

/* Unlike indexBy, groupBy preserves all items with the same key */
products = [
    [name: 'Apple', category: 'fruit'],
    [name: 'Carrot', category: 'vegetable'],
    [name: 'Banana', category: 'fruit']
];
byCategory = products->groupBy(^#.category);
/* [
    fruit: [[name: 'Apple', ...], [name: 'Banana', ...]],
    vegetable: [[name: 'Carrot', ...]]
] */
```

**`zip(other)` - Combine two arrays/tuples into array/tuple of tuples**
```walnut
/* Signature - Arrays */
^[Array<T1, minL1..maxL1>, Array<T2, minL2..maxL2>]
  => Array<(T1, T2), min(minL1,minL2)..min(maxL1,maxL2)>

/* Signature - Tuples (preserves tuple type) */
^[[T1, T2, T3], [U1, U2, U3]] => [[(T1, U1)], [(T2, U2)], [(T3, U3)]]

/* Examples - Arrays */
/* Basic pairing */
[1, 2, 3]->zip(['a', 'b', 'c']);
/* [[(1, 'a')], [(2, 'b')], [(3, 'c')]] : Array<(Integer, String)> */

/* Truncates to shortest */
[1, 2, 3, 4]->zip(['a', 'b']);
/* [[(1, 'a')], [(2, 'b')]] : Array<(Integer, String), 2> */

/* Combine parallel data */
names = ['Alice', 'Bob', 'Charlie'];
ages = [30, 25, 35];
names->zip(ages);
/* [[('Alice', 30)], [('Bob', 25)], [('Charlie', 35)]] */

/* Use with destructuring */
pairs = [1, 2, 3]->zip([10, 20, 30]);
pairs->map(^[(a, b)] :: a + b);
/* [11, 22, 33] */

/* Common pattern: zip then process */
headers = ['id', 'name', 'email'];
values = [42, 'Alice', 'alice@example.com'];
headers->zip(values)->map(^[(key, val)] :: key + ': ' + val->asString);
/* ['id: 42', 'name: Alice', 'email: alice@example.com'] */

/* Examples - Tuples (result is also a Tuple) */
tuple1 = ['Alice', 30, true];
tuple2 = [1.75, false, 'active'];
tuple1->zip(tuple2);
/* [[('Alice', 1.75)], [(30, false)], [(true, 'active')]] : [[String, Real], [Integer, Boolean], [Boolean, String]] */

/* Tuples with rest types */
t1 = ['a', 1, true, ...moreValues];  /* [String, Integer, Boolean, ...T] */
t2 = [1.5, false, 42];
t1->zip(t2);  /* Result includes rest handling */
```

**`zipMap(values)` - Combine array of keys with array of values into a Map**
```walnut
/* Signature */
^[Array<String, minL1..maxL1>, Array<T, minL2..maxL2>] => Map<String:T, 1..min(maxL1,maxL2)>

/* Examples */
/* Basic key-value pairing */
keys = ['name', 'age', 'city'];
values = ['Alice', 30, 'NYC'];
keys->zipMap(values);
/* [name: 'Alice', age: 30, city: 'NYC'] : Map<String|Integer> */

/* Truncates to shortest length */
['a', 'b', 'c']->zipMap([1, 2]);
/* [a: 1, b: 2] : Map<Integer, 2> */

['a', 'b']->zipMap([1, 2, 3, 4]);
/* [a: 1, b: 2] : Map<Integer, 2> */

/* CSV/TSV parsing */
headers = ['id', 'name', 'email'];
row = ['42', 'Bob', 'bob@example.com'];
record = headers->zipMap(row);
/* [id: '42', name: 'Bob', email: 'bob@example.com'] */

/* Dynamic record construction */
buildRecord = ^[Array<String>, Array<Integer>] => Map<String:Integer> ::
    headers->zipMap(values);

/* Duplicate keys: last value wins */
['key', 'name', 'key']->zipMap([1, 'Alice', 42]);
/* [key: 42, name: 'Alice'] - second 'key' overwrites first */
```

**`toMap(keyExtractor)` - Convert to map**
```walnut
/* Signature */
^[Array<T>, ^T => String] => Map<T>

/* Examples */
users = [
    [id: 1, name: 'Alice'],
    [id: 2, name: 'Bob']
];

users->toMap(^#.id->asString);
/* Map<User> with keys '1' and '2' */
```

## 25.3 Mutable Array Operations

Walnut provides special mutable operations for arrays. These modify the array in place.

### 25.3.1 Push and Pop

**`PUSH(array, value)` - Add element to end (mutable)**
```walnut
/* Signature */
^[Mutable<Array<T>>, T] => Null

/* Example */
=> {
    arr = mutable{Array<Integer>, [1, 2, 3]};
    PUSH(arr, 4);
    arr->printed;                        /* [1, 2, 3, 4] */
};
```

**`POP(array)` - Remove last element (mutable)**
```walnut
/* Signature */
^Mutable<Array<T>> => T | Null

/* Example */
=> {
    arr = mutable{Array<Integer>, [1, 2, 3]};
    last = POP(arr);                     /* 3 */
    arr->printed;                        /* [1, 2] */
};
```

### 25.3.2 Shift and Unshift

**`UNSHIFT(array, value)` - Add element to beginning (mutable)**
```walnut
/* Signature */
^[Mutable<Array<T>>, T] => Null

/* Example */
=> {
    arr = mutable{Array<Integer>, [2, 3, 4]};
    UNSHIFT(arr, 1);
    arr->printed;                        /* [1, 2, 3, 4] */
};
```

**`SHIFT(array)` - Remove first element (mutable)**
```walnut
/* Signature */
^Mutable<Array<T>> => T | Null

/* Example */
=> {
    arr = mutable{Array<Integer>, [1, 2, 3]};
    first = SHIFT(arr);                  /* 1 */
    arr->printed;                        /* [2, 3] */
};
```

## 25.4 Tuple Types

### 25.4.1 Basic Tuple Type

Tuples are fixed-size collections where each position can have a different type.

**Syntax:**
```walnut
(Type1, Type2, Type3, ...)
```

**Examples:**
```walnut
/* Basic tuples */
point = (10, 20);                        /* Type: (Integer, Integer) */
person = ('Alice', 30, true);            /* Type: (String, Integer, Boolean) */
empty = ();                              /* Type: () - unit type */

/* Mixed types */
mixed = (1, 'two', 3.0, true);           /* Type: (Integer, String, Real, Boolean) */
```

### 25.4.2 Named Tuple Types

Tuples can have type aliases for better semantics.

**Examples:**
```walnut
/* Type aliases */
Point2D = (Integer, Integer);
Point3D = (Integer, Integer, Integer);
RGB = (Integer, Integer, Integer);

/* Usage */
origin = (0, 0);                             /* Type: Point2D */
color = (255, 128, 0);                       /* Type: RGB */

/* Function with tuple parameter */
distance = ^Point2D => Real :: {
    x = $->item(0);
    y = $->item(1);
    ((x * x + y * y)->asReal)->sqrt
};

distance((3, 4));                        /* 5.0 */
```

### 25.4.3 Tuple Decomposition

Tuples can be destructured in variable declarations.

**Examples:**
```walnut
/* Destructuring */
point = (10, 20);
(x, y) = point;                          /* x = 10, y = 20 */

/* Nested destructuring */
nested = ((1, 2), (3, 4));
((a, b), (c, d)) = nested;               /* a=1, b=2, c=3, d=4 */

/* Ignoring elements */
triple = (1, 2, 3);
(first, ~, third) = triple;              /* first=1, third=3, skip middle */
```

## 25.5 Tuple Operations

### 25.5.1 Basic Properties

**`length` - Get tuple length**
```walnut
/* Signature */
^(T1, T2, ..., Tn) => Integer

/* Examples */
(1, 2)->length;                          /* 2 */
('a', 'b', 'c')->length;                 /* 3 */
()→length;                               /* 0 */
```

**`item(index)` - Access element by index**
```walnut
/* Signature */
^[(T1, T2, ..., Tn), Integer] => Ti

/* Examples */
point = (10, 20, 30);
point->item(0);                          /* 10 */
point->item(1);                          /* 20 */
point->item(2);                          /* 30 */
point->item(-1);                         /* 30 (last element) */
```

### 25.5.2 Conversion

**`itemValues()` - Convert tuple to array**
```walnut
/* Signature */
^(T1, T2, ..., Tn) => Array<T1 | T2 | ... | Tn>

/* Examples */
(1, 2, 3)->itemValues();                 /* [1, 2, 3] */
('a', 'b', 'c')->itemValues();           /* ['a', 'b', 'c'] */

/* Mixed types result in union type */
(1, 'two', 3.0)->itemValues();           /* Array<Integer | String | Real> */
```

## 25.6 Casting and Conversion

### 25.6.1 Array to String

Arrays can be converted to strings using `asString` or `printed`.

**`asString` - Default string representation**
```walnut
/* Examples */
[1, 2, 3]->asString;                     /* '[1, 2, 3]' */
['a', 'b']->asString;                    /* "['a', 'b']" */
[]->asString;                            /* '[]' */

/* Nested arrays */
[[1, 2], [3, 4]]->asString;              /* '[[1, 2], [3, 4]]' */
```

**`join(separator)` - Custom string representation**
```walnut
/* Examples */
[1, 2, 3]->join(', ');                   /* '1, 2, 3' */
['apple', 'banana']->join(' and ');      /* 'apple and banana' */
```

### 25.6.2 String to Array

Strings can be converted to arrays using `split`.

**Examples:**
```walnut
'hello world'->split(' ');               /* ['hello', 'world'] */
'a,b,c,d'->split(',');                   /* ['a', 'b', 'c', 'd'] */
'abc'->split('');                        /* ['a', 'b', 'c'] */
```

### 25.6.3 Array to Map

Arrays can be converted to maps with custom key extraction.

**Examples:**
```walnut
users = [
    [id: 1, name: 'Alice'],
    [id: 2, name: 'Bob']
];

users->toMap(^#.id->asString);
/* Map with keys '1' and '2' */
```

### 25.6.4 Tuple to Array

Tuples can be converted to arrays using `itemValues()`.

**Examples:**
```walnut
(1, 2, 3)->itemValues();                 /* [1, 2, 3] */
('a', 'b', 'c')->itemValues();           /* ['a', 'b', 'c'] */
```

### 25.6.5 Array to Tuple

Arrays cannot be directly converted to tuples because tuples have fixed size and heterogeneous types determined at compile time. However, you can construct tuples from array elements:

**Examples:**
```walnut
arr = [1, 2, 3];
tuple = (arr->item(0), arr->item(1), arr->item(2));
/* tuple: (Integer, Integer, Integer) */
```

## 25.7 Practical Examples

### 25.7.1 Data Processing

```walnut
/* Calculate statistics */
calculateStats = ^Array<Real> => [mean: Real, min: Real, max: Real, count: Integer] :: {
    [
        mean: $->sum() / $->length->asReal,
        min: ?whenIsError($->min()) { 0.0 },
        max: ?whenIsError($->max()) { 0.0 },
        count: $->length
    ]
};

scores = [85.5, 92.0, 78.5, 95.0, 88.0];
stats = calculateStats(scores);
/* [mean: 87.8, min: 78.5, max: 95.0, count: 5] */
```

### 25.7.2 Filtering and Transformation

```walnut
/* Process user data */
users = [
    [name: 'Alice', age: 30, active: true],
    [name: 'Bob', age: 25, active: false],
    [name: 'Charlie', age: 35, active: true],
    [name: 'David', age: 28, active: false]
];

/* Get active users over 25 */
activeAdults = users
    ->filter(^#.active)
    ->filter(^#.age > 25)
    ->map(^#.name);
/* ['Alice', 'Charlie'] */

/* Count by status */
activeCount = users->count(^#.active);   /* 2 */
inactiveCount = users->count(^!#.active);/* 2 */
```

### 25.7.3 Array Building

```walnut
/* Build array programmatically */
buildRange = ^[start: Integer, end: Integer, step: Integer] => Array<Integer> :: {
    start->upTo(end)->filter(^(# - start) % step == 0)
};

buildRange([start: 0, end: 20, step: 3]);
/* [0, 3, 6, 9, 12, 15, 18] */

/* Fibonacci sequence */
fibonacci = ^Integer => Array<Integer> :: {
    ?when($ <= 0) {
        []
    } ~ ?when($ == 1) {
        [0]
    } ~ {
        result = mutable{Array<Integer>, [0, 1]};
        i = mutable{Integer, 2};
        ?whileTrue(^ :: i->value < $) {
            a = result->item(i->value - 2);
            b = result->item(i->value - 1);
            PUSH(result, a + b);
            i->SET(i->value + 1);
        };
        result
    }
};

fibonacci(10);
/* [0, 1, 1, 2, 3, 5, 8, 13, 21, 34] */
```

### 25.7.4 Grouping and Aggregation

```walnut
/* Group by property */
groupBy = ^[Array<T>, ^T => String] => Map<Array<T>> :: {
    $->reduce(
        [:],  /* empty map */
        ^[#acc, #item] :: {
            key = (#item->($$));
            existing = ?whenIsError(#acc->item(key)) { [] };
            #acc->with(key, existing->append(#item))
        }
    )
};

products = [
    [name: 'Apple', category: 'Fruit', price: 1.99],
    [name: 'Banana', category: 'Fruit', price: 0.99],
    [name: 'Carrot', category: 'Vegetable', price: 0.79],
    [name: 'Broccoli', category: 'Vegetable', price: 1.49]
];

grouped = products->groupBy(^#.category);
/* Map with keys 'Fruit' and 'Vegetable' */
```

### 25.7.5 Tuple Usage

```walnut
/* Point operations */
Point2D = (Integer, Integer);

addPoints = ^[Point2D, Point2D] => Point2D :: {
    (x1, y1) = $->item(0);
    (x2, y2) = $->item(1);
    (x1 + x2, y1 + y2)
};

scalePoint = ^[Point2D, Integer] => Point2D :: {
    (x, y) = $->item(0);
    factor = $->item(1);
    (x * factor, y * factor)
};

p1 = (10, 20);
p2 = (5, 15);
sum = addPoints([p1, p2]);               /* (15, 35) */
scaled = scalePoint([p1, 3]);            /* (30, 60) */
```

### 25.7.6 Matrix Operations

```walnut
/* Matrix as Array<Array<Integer>> */
Matrix = Array<Array<Integer>>;

createMatrix = ^[rows: Integer, cols: Integer, value: Integer] => Matrix :: {
    0->upTo(rows - 1)->map(^~ :: {
        0->upTo(cols - 1)->map(^~ :: value)
    })
};

transposeMatrix = ^Matrix => Matrix :: {
    rows = $->length;
    cols = $->item(0)->length;
    0->upTo(cols - 1)->map(^col :: {
        0->upTo(rows - 1)->map(^row :: {
            $->item(row)->item(col)
        })
    })
};

matrix = [
    [1, 2, 3],
    [4, 5, 6]
];

transposed = transposeMatrix(matrix);
/* [
    [1, 4],
    [2, 5],
    [3, 6]
] */
```

## 25.8 Best Practices

### 25.8.1 Use Type Refinements

```walnut
/* Good: Use refined types for clarity and safety */
NonEmptyIntegers = Array<Integer, 1..>;
SmallStringList = Array<String, 1..10>;
RGB = Array<Integer<0..255>, 3>;

colors = [255, 128, 0];                      /* Type: Array<Integer, 3> */
```

### 25.8.2 Prefer Immutable Operations

```walnut
/* Good: Use immutable operations */
newArray = oldArray->append(newItem);

/* Avoid: Mutable operations unless necessary */
=> {
    arr = mutable{Array<Any>, oldArray};
    PUSH(arr, newItem);
};
```

### 25.8.3 Use Functional Transformations

```walnut
/* Good: Chain functional operations */
result = data
    ->filter(^#.active)
    ->map(^#.name)
    ->sort();

/* Avoid: Imperative loops with mutable state - use functional transformations instead */
```

### 25.8.4 Handle Empty Arrays

```walnut
/* Good: Check for empty arrays */
firstItem = ?when(arr->length > 0) {
    arr->item(0)
} ~ {
    null
};

/* Or use error handling */
firstItem = ?whenIsError(arr->item(0)) { null };

/* Good: Provide defaults for aggregations */
avg = ?when(arr->length > 0) {
    arr->sum()->asReal / arr->length->asReal
} ~ {
    0.0
};
```

### 25.8.5 Use Tuples for Fixed Data

```walnut
/* Good: Use tuples for fixed, heterogeneous data */
Point2D = (Integer, Integer);
RGB = (Integer, Integer, Integer);

point = (10, 20);
color = (255, 128, 0);

/* Avoid: Arrays for fixed data */
point = [10, 20];                        /* Type: Array<Integer> - less specific */
```

### 25.8.6 Decompose Tuples

```walnut
/* Good: Decompose tuples for readability */
point = (10, 20);
(x, y) = point;
distance = ((x * x + y * y)->asReal)->sqrt;

/* Avoid: Repeated item() calls */
distance = ((point->item(0) * point->item(0) +
             point->item(1) * point->item(1))->asReal)->sqrt;
```

## 25.9 Performance Considerations

### 25.9.1 Array Growth

Immutable append operations create new arrays. For building large arrays, consider using mutable operations:

### 25.9.2 Early Termination

Use `findFirst` instead of `filter` when you only need one result:

```walnut
/* Good: Stop at first match */
firstMatch = items->findFirst(^#.id == targetId);

/* Avoid: Check entire array */
firstMatch = items->filter(^#.id == targetId)->item(0);
```

### 25.9.3 Reduce Operations

Combine operations with `reduce` to avoid multiple array traversals:

```walnut
/* Good: Single pass */
stats = numbers->reduce(
    [sum: 0, count: 0, positives: 0],
    ^[#acc, #val] :: [
        sum: #acc.sum + #val,
        count: #acc.count + 1,
        positives: #acc.positives + ?when(#val > 0) { 1 } ~ { 0 }
    ]
);

/* Avoid: Multiple passes */
sum = numbers->sum();
count = numbers->length;
positives = numbers->count(^# > 0);
```

## Summary

Walnut's array and tuple system provides:

**Arrays:**
- **Dynamic collections** with homogeneous element types
- **Type refinements** for length and element constraints
- **Immutable by default** with mutable operations available
- **Rich operations**: searching, filtering, mapping, reducing, sorting
- **Aggregation functions**: sum, product, min, max, all, any
- **Slicing and padding** operations

**Tuples:**
- **Fixed-size collections** with heterogeneous types
- **Position-specific types** known at compile time
- **Decomposition support** for easy access
- **Conversion to arrays** via `itemValues()`

**Key Features:**
- Type-safe operations
- Functional programming patterns
- Method chaining support
- Integration with other collection types
- Performance-conscious operations

Best practices include:
- Use type refinements for clarity
- Prefer immutable operations
- Use functional transformations
- Handle empty arrays gracefully
- Choose appropriate collection type (array vs tuple)
- Consider performance for large datasets
