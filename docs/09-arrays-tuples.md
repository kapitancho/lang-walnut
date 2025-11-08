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

**`reduce(initial, accumulator)` - Reduce to single value**
```walnut
/* Signature */
^[Array<T>, U, ^[U, T] => U] => U

/* Examples */
/* Sum all numbers */
[1, 2, 3, 4, 5]->reduce(0, ^[#acc, #val] :: #acc + #val);  /* 15 */

/* Concatenate strings */
['hello', ' ', 'world']->reduce('', ^[#acc, #val] :: #acc + #val);
/* 'hello world' */

/* Count occurrences */
[1, 2, 2, 3, 2, 4]->reduce(
    [count: 0],
    ^[#acc, #val] :: ?when(#val == 2) {
        [count: #acc.count + 1]
    } ~ {
        #acc
    }
);                                       /* [count: 3] */
```

**`sort()` - Sort array in ascending order**
```walnut
/* Signature */
^Array<T> => Array<T>  /* where T is comparable */

/* Examples */
[3, 1, 4, 1, 5, 9]->sort();              /* [1, 1, 3, 4, 5, 9] */
['charlie', 'alice', 'bob']->sort();     /* ['alice', 'bob', 'charlie'] */
```

**`sortDescending()` - Sort array in descending order**
```walnut
/* Signature */
^Array<T> => Array<T>  /* where T is comparable */

/* Examples */
[3, 1, 4, 1, 5, 9]->sortDescending();    /* [9, 5, 4, 3, 1, 1] */
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

**`flatten()` - Flatten nested array by one level**
```walnut
/* Signature */
^Array<Array<T>> => Array<T>

/* Examples */
[[1, 2], [3, 4], [5, 6]]->flatten();     /* [1, 2, 3, 4, 5, 6] */
[['a'], ['b', 'c'], ['d']]->flatten();   /* ['a', 'b', 'c', 'd'] */
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
^[Array<T>, Integer] => Array<T>

/* Examples */
[1, 2, 3, 4, 5]->take(3);                /* [1, 2, 3] */
['a', 'b', 'c']->take(2);                /* ['a', 'b'] */
[1, 2]->take(5);                         /* [1, 2] (no error if too few) */
```

**`skip(count)` - Skip first n elements**
```walnut
/* Signature */
^[Array<T>, Integer] => Array<T>

/* Examples */
[1, 2, 3, 4, 5]->skip(2);                /* [3, 4, 5] */
['a', 'b', 'c', 'd']->skip(1);           /* ['b', 'c', 'd'] */
[1, 2]->skip(5);                         /* [] (no error if too many) */
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
>>> {
    mut arr = [1, 2, 3];
    PUSH(arr, 4);
    arr->printed;                        /* [1, 2, 3, 4] */
};
```

**`POP(array)` - Remove last element (mutable)**
```walnut
/* Signature */
^Mutable<Array<T>> => T | Null

/* Example */
>>> {
    mut arr = [1, 2, 3];
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
>>> {
    mut arr = [2, 3, 4];
    UNSHIFT(arr, 1);
    arr->printed;                        /* [1, 2, 3, 4] */
};
```

**`SHIFT(array)` - Remove first element (mutable)**
```walnut
/* Signature */
^Mutable<Array<T>> => T | Null

/* Example */
>>> {
    mut arr = [1, 2, 3];
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
        mut result = [0, 1];
        mut i = 2;
        ?whileTrue(^ :: i < $) {
            a = result->item(i - 2);
            b = result->item(i - 1);
            PUSH(result, a + b);
            i = i + 1;
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
>>> {
    mut arr = oldArray;
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

/* Avoid: Imperative loops */
>>> {
    mut result = [];
    ?forEach(data) as item {
        ?when(item.active) {
            PUSH(result, item.name);
        };
    };
    result->sort()
};
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

```walnut
/* For many appends, use mutable operations */
buildLargeArray = ^Integer => Array<Integer> :: {
    >>> {
        mut result = [];
        0->upTo($ - 1)->forEach(^item :: {
            PUSH(result, item * item);
        });
        result
    }
};
```

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
