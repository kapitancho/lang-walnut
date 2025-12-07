# 22. Integers and Reals

## Overview

Walnut provides two numeric types: `Integer` for whole numbers and `Real` for decimal numbers. Both types support arbitrary precision through BcMath, allowing representation of numbers of any size or precision (limited only by available memory).

## 22.1 Integer Type

### 22.1.1 Type Definition

**Syntax:**
- `Integer` - all integers (unlimited range)
- `Integer<min..max>` - integers in a closed range [min, max]
- `Integer<min..>` - integers from min onwards
- `Integer<..max>` - integers up to max

**Examples:**
```walnut
Age = Integer<0..150>;
PositiveInt = Integer<1..>;
NegativeOrZero = Integer<..0>;
SmallNumber = Integer<-10..10>;
```

**Type inference:**
```walnut
x = 42;            /* Type: Integer[42] (singleton subset) */
y = 50;            /* Type: Integer[50] */
large = 99999999;  /* Type: Integer[99999999] - no size limit */
```

### 22.1.2 Precision and Limits

**Arbitrary Precision:**
- Integers have **no inherent bit-width limitations** (no 32-bit or 64-bit constraints)
- Internally uses **BcMath** for arbitrary-precision arithmetic
- Can represent arbitrarily large integers (limited only by available memory)
- Range constraints (e.g., `Integer<min..max>`) can be used to explicitly limit the value range when needed
- When working with JSON values, extremely large numbers may be problematic due to JSON parser limitations

**Examples:**
```walnut
/* All of these are valid integers */
small = 42;
large = 123456789012345678901234567890;
veryLarge = 999999999999999999999999999999999999999999;

/* With range constraints */
Age = Integer<0..150>;        /* Explicitly limited */
Unlimited = Integer;          /* No limits */
PositiveInt = Integer<1..>;   /* Lower bound only */
```

### 22.1.3 Integer Literals

**Syntax:** Optional `-` sign followed by decimal digits.

**Examples:**
```walnut
42
0
-17
1000000
123456789012345678901234567890
```

### 22.1.4 Integer Subsets

Integer subsets define finite sets of allowed integer values.

**Syntax:** `Integer[value1, value2, value3, ...]`

**Examples:**
```walnut
DiceRoll = Integer[1, 2, 3, 4, 5, 6];
PrimeDigit = Integer[2, 3, 5, 7];
HttpSuccess = Integer[200, 201, 202, 204];

roll = 5;  /* Type: Integer[5], can be assigned to DiceRoll */
```

### 22.1.5 Integer Arithmetic Operations

#### Addition

```walnut
Integer->binaryPlus(Integer => Integer)

5 + 3;   /* 8 */
-10 + 5; /* -5 */
```

#### Subtraction

```walnut
Integer->binaryMinus(Integer => Integer)

10 - 4;  /* 6 */
5 - 10;  /* -5 */
```

#### Multiplication

```walnut
Integer->binaryMultiply(Integer => Integer)

6 * 7;   /* 42 */
-3 * 4;  /* -12 */
```

#### Division (returns Real)

```walnut
Integer->binaryDivide(Integer => Result<Real, NotANumber>)

10 / 2;  /* 5.0 */
10 / 3;  /* 3.333... */
10 / 0;  /* @NotANumber */
```

#### Integer Division

```walnut
Integer->binaryIntegerDivide(Integer => Result<Integer, NotANumber>)

10 // 3;  /* 3 */
10 // 2;  /* 5 */
10 // 0;  /* @NotANumber */
```

#### Modulo

```walnut
Integer->binaryModulo(Integer => Result<Integer, NotANumber>)

10 % 3;  /* 1 */
17 % 5;  /* 2 */
10 % 0;  /* @NotANumber */
```

#### Exponentiation

```walnut
Integer->binaryPower(Integer => Integer)

2 ** 8;  /* 256 */
3 ** 3;  /* 27 */
10 ** 6; /* 1000000 */
```

#### Unary Plus

```walnut
Integer->unaryPlus(Null => Integer)

+42;  /* 42 */
```

#### Unary Minus (Negation)

```walnut
Integer->unaryMinus(Null => Integer)

-42;   /* -42 */
-(-5); /* 5 */
```

### 22.1.6 Integer Comparison Operations

#### Greater Than

```walnut
Integer->binaryGreaterThan(Integer => Boolean)

10 > 5;   /* true */
5 > 10;   /* false */
```

#### Greater Than or Equal

```walnut
Integer->binaryGreaterThanEqual(Integer => Boolean)

10 >= 10;  /* true */
10 >= 5;   /* true */
5 >= 10;   /* false */
```

#### Less Than

```walnut
Integer->binaryLessThan(Integer => Boolean)

3 < 5;   /* true */
5 < 3;   /* false */
```

#### Less Than or Equal

```walnut
Integer->binaryLessThanEqual(Integer => Boolean)

5 <= 5;  /* true */
3 <= 5;  /* true */
5 <= 3;  /* false */
```

#### Equality

```walnut
Integer->binaryEqual(Integer => Boolean)

42 == 42;  /* true */
42 == 43;  /* false */
```

#### Inequality

```walnut
Integer->binaryNotEqual(Integer => Boolean)

42 != 43;  /* true */
42 != 42;  /* false */
```

### 22.1.7 Integer Bitwise Operations

#### Bitwise AND

```walnut
Integer->binaryBitwiseAnd(Integer => Integer)

12 & 10;  /* 8 (binary: 1100 & 1010 = 1000) */
```

#### Bitwise OR

```walnut
Integer->binaryBitwiseOr(Integer => Integer)

12 | 10;  /* 14 (binary: 1100 | 1010 = 1110) */
```

#### Bitwise XOR

```walnut
Integer->binaryBitwiseXor(Integer => Integer)

12 ^ 10;  /* 6 (binary: 1100 ^ 1010 = 0110) */
```

#### Bitwise NOT

```walnut
Integer->unaryBitwiseNot(Null => Integer)

~5;   /* -6 (two's complement) */
~(-1); /* 0 */
```

### 22.1.8 Integer Math Functions

#### Absolute Value

```walnut
Integer->abs(Null => Integer<0..>)

(-42)->abs;  /* 42 */
5->abs;      /* 5 */
```

#### Square

```walnut
Integer->square(Null => Integer<0..>)

5->square;  /* 25 */
(-3)->square; /* 9 */
```

#### Clamp

```walnut
Integer->clamp([min: Integer, max: Integer] => Result<Integer, InvalidIntegerRange>)

/* Signature with optional min/max */
^[Integer, [min: ?Integer, max: ?Integer]] => Result<Integer, InvalidIntegerRange>

/* Signature with at least one of min/max Real */
^[Integer, [min: ?Real, max: ?Real]] => Result<Real, InvalidRealRange>

/* In case that min and max are guaranteed to be a valid range, the return type can be simplified to Integer or Real respectively */

/* Examples */
/* Clamp within range */
5->clamp[min: 0, max: 10];               /* 5 (within bounds) */
-5->clamp[min: 0, max: 10];              /* 0 (clamped to min) */
15->clamp[min: 0, max: 10];              /* 10 (clamped to max) */

/* Clamp with only min */
5->clamp[min: 10];                       /* 10 (below minimum) */
15->clamp[min: 10];                      /* 15 (above minimum, no max) */

/* Clamp with only max */
5->clamp[max: 10];                       /* 5 (below maximum) */
15->clamp[max: 10];                      /* 10 (above maximum) */

/* No constraints - returns value unchanged */
5->clamp[:];                             /* 5 */

/* Practical use - constrain user input */
userInput = -50;
percentage = userInput->clamp[min: 0, max: 100];  /* 0 */
```

#### Digits

```walnut
Integer->digits(=> Array<Integer<0..9>>)

/* Examples */
/* Single digit */
5->digits;                               /* [5] */
0->digits;                               /* [0] */

/* Multiple digits */
42->digits;                              /* [4, 2] */
123->digits;                             /* [1, 2, 3] */
1000->digits;                            /* [1, 0, 0, 0] */

/* Large number */
987654321->digits;                       /* [9, 8, 7, 6, 5, 4, 3, 2, 1] */

/* Practical use - sum of digits */
sumDigits = ^n: Integer<0..> => Integer ::
    n->digits->reduce[
        reducer: ^[result: Integer, item: Integer<0..9>] => Integer ::
            #result + #item,
        initial: 0
    ];

sumDigits(123);                          /* 6 (1 + 2 + 3) */

/* Check for specific digits */
hasDigit7 = ^n: Integer<0..> => Boolean ::
    n->digits->any(^# == 7);

hasDigit7(1723);                         /* true */
hasDigit7(1234);                         /* false */

/* Type inference - digits are always 0..9 */
digitCheck = ^v: Integer<0..9> => Array<Integer<0..9>, 1> ::
    v->digits;
```

**Note:** The `digits` method only works with non-negative integers. For negative numbers, take the absolute value first:

```walnut
num = -123;
digitArray = num->abs->digits;           /* [1, 2, 3] */
```

### 22.1.9 Integer Range Generation

#### Ascending Range

```walnut
Integer->upTo(Integer => Array<Integer>)

2->upTo(5);   /* [2, 3, 4, 5] */
0->upTo(3);   /* [0, 1, 2, 3] */
```

#### Descending Range

```walnut
Integer->downTo(Integer => Array<Integer>)

5->downTo(2);  /* [5, 4, 3, 2] */
3->downTo(0);  /* [3, 2, 1, 0] */
```

### 22.1.10 Casting From Integer

#### To Real

```walnut
Integer->asReal(Null => Real)

42->asReal;     /* 42.0 */
(-5)->asReal;   /* -5.0 */
```

#### To String

```walnut
Integer->asString(Null => String)

42->asString;     /* '42' */
(-17)->asString;  /* '-17' */
0->asString;      /* '0' */
```

#### To Boolean

```walnut
Integer->asBoolean(Null => Boolean)

42->asBoolean;  /* true (any non-zero) */
0->asBoolean;   /* false */
(-5)->asBoolean; /* true */
```

### 22.1.11 Casting To Integer

#### From Real

```walnut
Real->asInteger(Null => Integer)

3.14->asInteger;   /* 3 (truncates) */
3.9->asInteger;    /* 3 */
(-3.7)->asInteger; /* -3 */
```

#### From String

```walnut
String->asInteger(Null => Result<Integer, NotANumber>)

'42'->asInteger;    /* 42 */
'-17'->asInteger;   /* -17 */
'3.14'->asInteger;  /* @NotANumber */
'abc'->asInteger;   /* @NotANumber */
''->asInteger;      /* @NotANumber */
```

#### From Boolean

```walnut
Boolean->asInteger(Null => Integer<0..1>)

true->asInteger;   /* 1 */
false->asInteger;  /* 0 */
```

## 22.2 Real Type

### 22.2.1 Type Definition

**Syntax:**
- `Real` - all real numbers
- `Real<min..max>` - reals in a closed range [min, max]
- `Real<(min..max)>` - reals in an open range (min, max)
- `Real<[min..max)>` - reals in a half-open range [min, max)
- `Real<(min..max]>` - reals in a half-open range (min, max]
- `Real<min..>` - reals from min onwards
- `Real<..max>` - reals up to max

**Examples:**
```walnut
Temperature = Real<-273.15..>;     /* Absolute zero and above */
Probability = Real<0..1>;          /* Closed interval [0, 1] */
PositiveReal = Real<(0..)>;        /* Open interval (0, +∞) */
NonZero = Real<(..-0), (0..)>;     /* All reals except zero */
```

**Advanced range syntax:**
```walnut
/* Multiple intervals */
ValidRange = Real<(..-2], [1..5.29), (6.43..)>;
/* Represents: (-∞, -2] ∪ [1, 5.29) ∪ (6.43, +∞) */
```

**Type inference:**
```walnut
pi = 3.14159;  /* Type: Real[3.14159] */
e = 2.71828;   /* Type: Real[2.71828] */
```

### 22.2.2 Precision and Limits

**Arbitrary Precision:**
- Real numbers have **no inherent bit-width limitations** (no 32-bit or 64-bit float/double constraints)
- Internally uses **BcMath** for arbitrary-precision decimal arithmetic
- Can represent numbers with arbitrary precision (limited only by available memory)
- Range constraints (e.g., `Real<min..max>`) can be used to explicitly limit the value range when needed
- When working with JSON values, extremely large numbers or very high precision may be problematic due to JSON parser limitations

**Examples:**
```walnut
/* All of these are valid reals */
small = 3.14;
precise = 3.141592653589793238462643383279502884197;
verySmall = 0.00000000000000000000000000000001;
veryLarge = 123456789012345678901234567890.123456789;

/* With range constraints */
Probability = Real<0..1>;         /* Explicitly limited */
Unlimited = Real;                 /* No limits */
PositiveReal = Real<(0..)>;       /* Lower bound only (exclusive) */
```

### 22.2.3 Real Literals

**Syntax:** Optional `-` sign, decimal digits, `.`, decimal digits.

**Examples:**
```walnut
3.14
-2.71
0.5
100.0
3.141592653589793238462643383279502884197
```

### 22.2.4 Real Subsets

Real subsets define finite sets of allowed real values.

**Syntax:** `Real[value1, value2, value3, ...]`

**Examples:**
```walnut
MathConstants = Real[3.14159, 2.71828, 1.61803];
Benchmarks = Real[0.0, 0.5, 1.0];

pi = 3.14159;  /* Type: Real[3.14159], can be assigned to MathConstants */
```

### 22.2.5 Real Arithmetic Operations

Real supports the same arithmetic operators as Integer:

```walnut
3.14 + 2.71;   /* 5.85 */
10.5 - 3.2;    /* 7.3 */
2.5 * 4.0;     /* 10.0 */
10.0 / 3.0;    /* 3.333... */
10.5 % 3.0;    /* 1.5 */
2.0 ** 3.0;    /* 8.0 */
+3.14;         /* 3.14 */
-3.14;         /* -3.14 */
```

### 22.2.6 Real Comparison Operations

Real supports the same comparison operators as Integer:

```walnut
3.14 > 2.71;   /* true */
3.14 >= 3.14;  /* true */
2.71 < 3.14;   /* true */
2.71 <= 2.71;  /* true */
3.14 == 3.14;  /* true */
3.14 != 2.71;  /* true */
```

### 22.2.7 Real Math Functions

#### Absolute Value

```walnut
Real->abs(Null => Real<0..>)

(-3.14)->abs;  /* 3.14 */
5.5->abs;      /* 5.5 */
```

#### Square

```walnut
Real->square(Null => Real<0..>)

3.5->square;  /* 12.25 */
(-2.0)->square; /* 4.0 */
```

#### Square Root

```walnut
Real->sqrt(Null => Real<0..>)

25.0->sqrt;  /* 5.0 */
16.0->sqrt;  /* 4.0 */
2.0->sqrt;   /* 1.414... */
```

#### Natural Logarithm

```walnut
Real->ln(Null => Real)

2.71828->ln;  /* ~1.0 (ln(e) = 1) */
10.0->ln;     /* ~2.302... */
```

#### Floor Function

```walnut
Real->floor(Null => Integer)

3.7->floor;    /* 3 */
3.1->floor;    /* 3 */
(-3.7)->floor; /* -4 */
(-3.1)->floor; /* -4 */
```

#### Ceiling Function

```walnut
Real->ceil(Null => Integer)

3.2->ceil;    /* 4 */
3.9->ceil;    /* 4 */
(-3.2)->ceil; /* -3 */
(-3.9)->ceil; /* -3 */
```

#### Round to Decimal Places

```walnut
Real->roundAsDecimal(Integer<0..> => Real)

3.14159->roundAsDecimal(2);  /* 3.14 */
3.14159->roundAsDecimal(4);  /* 3.1416 */
2.71828->roundAsDecimal(3);  /* 2.718 */
```

#### Round to Nearest Integer

```walnut
Real->roundAsInteger(Null => Integer)

3.5->roundAsInteger;  /* 4 */
3.4->roundAsInteger;  /* 3 */
2.7->roundAsInteger;  /* 3 */
(-3.5)->roundAsInteger; /* -4 */
```

#### Clamp

```walnut
Real->clamp([min: Real, max: Real] => Result<Real, InvalidRealRange>)

/* Signature with optional min/max */
^[Real, [min?: Real, max?: Real]] => Result<Real, InvalidRealRange>

/* In case that min and max are guaranteed to be a valid range, the return type can be simplified to Real */

/* Examples */
/* Clamp within range */
5.5->clamp[min: 0.0, max: 10.0];         /* 5.5 (within bounds) */
-5.5->clamp[min: 0.0, max: 10.0];        /* 0.0 (clamped to min) */
15.5->clamp[min: 0.0, max: 10.0];        /* 10.0 (clamped to max) */

/* Clamp with only min */
5.5->clamp[min: 10.0];                   /* 10.0 (below minimum) */
15.5->clamp[min: 10.0];                  /* 15.5 (above minimum, no max) */

/* Clamp with only max */
5.5->clamp[max: 10.0];                   /* 5.5 (below maximum) */
15.5->clamp[max: 10.0];                  /* 10.0 (above maximum) */

/* No constraints - returns value unchanged */
5.5->clamp[:];                           /* 5.5 */

/* Practical use - constrain probability */
rawValue = 1.5;
probability = rawValue->clamp[min: 0.0, max: 1.0];  /* 1.0 */

/* Constrain temperature */
temp = -300.0;
validTemp = temp->clamp[min: -273.15];   /* -273.15 (absolute zero) */
```

### 22.2.8 Casting From Real

#### To Integer (Truncates)

```walnut
Real->asInteger(Null => Integer)

3.14->asInteger;   /* 3 */
3.9->asInteger;    /* 3 */
(-3.7)->asInteger; /* -3 */
```

#### To String

```walnut
Real->asString(Null => String)

3.14->asString;    /* '3.14' */
(-2.71)->asString; /* '-2.71' */
100.0->asString;   /* '100.0' or '100' */
```

#### To Boolean

```walnut
Real->asBoolean(Null => Boolean)

3.14->asBoolean;  /* true (any non-zero) */
0.0->asBoolean;   /* false */
(-2.5)->asBoolean; /* true */
```

### 22.2.9 Casting To Real

#### From Integer

```walnut
Integer->asReal(Null => Real)

42->asReal;     /* 42.0 */
(-5)->asReal;   /* -5.0 */
0->asReal;      /* 0.0 */
```

#### From String

```walnut
String->asReal(Null => Result<Real, NotANumber>)

'3.14'->asReal;   /* 3.14 */
'42'->asReal;     /* 42.0 */
'-2.5'->asReal;   /* -2.5 */
'abc'->asReal;    /* @NotANumber */
''->asReal;       /* @NotANumber */
```

#### From Boolean

```walnut
Boolean->asReal(Null => Real<0..1>)

true->asReal;   /* 1.0 */
false->asReal;  /* 0.0 */
```

## 22.3 Numeric Type Relationships

### 22.3.1 Subtyping

Integer is **not** a subtype of Real, and Real is **not** a subtype of Integer. They are distinct types that require explicit conversion.

```walnut
/* This is not valid: */
/* x: Real = 42;  /* Error: Integer is not a subtype of Real */

/* Use explicit conversion: */
x: Real = 42->asReal;  /* OK: 42.0 */

/* Or use union type: */
y: Integer|Real = 42;  /* OK */
z: Integer|Real = 3.14; /* OK */
```

### 22.3.2 Mixed Operations

Walnut does not automatically promote Integer to Real in operations. Use explicit conversions when mixing types:

```walnut
/* Integer + Real requires conversion */
intValue = 10;
realValue = 3.14;

/* Explicit conversion needed: */
sum = intValue->asReal + realValue;  /* 13.14 */

/* Or convert result: */
result = intValue / 5;  /* Result<Real, NotANumber> */
```

## 22.4 Practical Examples

### 22.4.1 Financial Calculations

```walnut
/* Money type with arbitrary precision */
Money = Real<0..>;

calculateTotal = ^items: Array<[price: Money, quantity: Integer]> => Money :: {
    items->map(^item => Money :: #price * #quantity->asReal)
        ->sum
};

/* With rounding */
formatPrice = ^price: Money => String :: {
    '$' + price->roundAsDecimal(2)->asString
};

items = [
    [price: 19.99, quantity: 3],
    [price: 5.50, quantity: 2],
    [price: 12.75, quantity: 1]
];

total = calculateTotal(items);  /* 84.72 */
formatted = formatPrice(total); /* '$84.72' */
```

### 22.4.2 Mathematical Computations

```walnut
/* Calculate distance between two points */
Point = [x: Real, y: Real];

distance = ^[p1: Point, p2: Point] => Real<0..> :: {
    dx = {#p2.x - #p1.x}->abs;
    dy = {#p2.y - #p1.y}->abs;
    {dx->square + dy->square}->sqrt
};

p1 = [x: 0.0, y: 0.0];
p2 = [x: 3.0, y: 4.0];
dist = distance([p1: p1, p2: p2]);  /* 5.0 */
```

### 22.4.3 Statistics

```walnut
/* Calculate average */
average = ^numbers: Array<Real, 1..> => Real :: {
    sum = numbers->sum;
    count = numbers->length->asReal;
    sum / count
};

/* Calculate standard deviation */
stdDev = ^numbers: Array<Real, 1..> => Real<0..> :: {
    avg = average(numbers);
    squaredDiffs = numbers->map(^x => Real :: {x - avg}->square);
    variance = average(squaredDiffs);
    variance->sqrt
};

data = [2.5, 3.7, 4.2, 5.1, 3.9];
avg = average(data);        /* 3.88 */
sd = stdDev(data);          /* ~0.98 */
```

### 22.4.4 Range Validation

```walnut
/* Temperature in Celsius */
Temperature = Real<-273.15..>;  /* Absolute zero and above */

/* Validate temperature */
validateTemp = ^temp: Real => Result<Temperature, String> :: {
    ?when(temp >= -273.15) {
        temp
    } ~ {
        @'Temperature below absolute zero'
    }
};

t1 = validateTemp(25.0);      /* OK: 25.0 */
t2 = validateTemp(-300.0);    /* Error */
```

### 22.4.5 Integer Sequences

```walnut
/* Generate Fibonacci sequence */
fibonacci = ^n: Integer<0..> => Array<Integer> :: {
    ?when(n == 0) { [] }
    ~ ?when(n == 1) { [0] }
    ~ {
        /* Build sequence iteratively */
        result = mutable{Array<Integer>, [0, 1]};
        2->upTo(n - 1)->forEach(^i :: {
            len = result->value->length;
            prev1 = ?whenIsError(result->value->item(len - 1)) { 0 };
            prev2 = ?whenIsError(result->value->item(len - 2)) { 0 };
            result->PUSH(prev1 + prev2)
        });
        result->value
    }
};

fib10 = fibonacci(10);  /* [0, 1, 1, 2, 3, 5, 8, 13, 21, 34] */
```

### 22.4.6 Prime Number Check

```walnut
/* Check if integer is prime */
isPrime = ^n: Integer<2..> => Boolean :: {
    ?when(n == 2) { true }
    ~ ?when(n % 2 == 0) { false }
    ~ {
        limit = n->asReal->sqrt->asInteger;
        3->upTo(limit)
            ->filter(^x => Boolean :: x % 2 != 0)  /* Odd numbers only */
            ->findFirst(^x => Boolean :: n % x == 0)
            ->isTypeOf(`ItemNotFound)  /* True if no divisor found */
    }
};

isPrime(17);  /* true */
isPrime(20);  /* false */
```

## 22.5 Best Practices

### 22.5.1 Use Appropriate Types

```walnut
/* Good: Specific types for domain */
Age = Integer<0..150>;
Price = Real<0..>;
Quantity = Integer<1..>;

/* Avoid: Generic types */
/* age: Integer */
/* price: Real */
```

### 22.5.2 Handle Division by Zero

```walnut
/* Good: Handle error case */
safeDivide = ^[numerator: Real, denominator: Real] => Result<Real, String> :: {
    ?when(#denominator == 0.0) {
        @'Division by zero'
    } ~ {
        #numerator / #denominator
    }
};

/* Or use Result type returned by / operator */
result = 10 / 0;  /* Result<Real, NotANumber> */
?whenTypeOf(result) is {
    `Real: result->printed,
    `NotANumber: 'Cannot divide by zero'
};
```

### 22.5.3 Use Explicit Conversions

```walnut
/* Good: Explicit conversions */
intValue = 42;
realValue = intValue->asReal;  /* Clear intent */

/* Avoid: Expecting implicit conversion */
/* realValue = intValue;  /* Error */
```

### 22.5.4 Round for Display

```walnut
/* Good: Round for user-facing output */
price = 19.999;
display = price->roundAsDecimal(2)->asString;  /* '20.00' */

/* Keep precision in calculations */
subtotal = price * 3->asReal;  /* 59.997 */
total = subtotal->roundAsDecimal(2);  /* 60.00 */
```

### 22.5.5 Use Range Types for Validation

```walnut
/* Good: Type-level validation */
Percentage = Real<0..100>;
Probability = Real<0..1>;

validatePercentage = ^value: Real => Result<Percentage, String> :: {
    ?when(value >= 0.0 && value <= 100.0) {
        value
    } ~ {
        @'Percentage must be between 0 and 100'
    }
};
```

## Summary

Walnut's numeric types provide:

- **Arbitrary precision** through BcMath - no size or precision limits
- **Type safety** with compile-time checking
- **Rich operations** for arithmetic, comparison, and math functions
- **Range constraints** for domain modeling
- **Subset types** for specific value sets
- **Explicit conversions** between types
- **Result types** for error handling (division by zero, parsing)

Key differences from other languages:
- **No automatic promotion** between Integer and Real
- **No bit-width limits** (32-bit, 64-bit) on numeric types
- **Arbitrary precision** for both integers and reals
- **Type-safe casting** with Result types
- **Compile-time range checking** when possible

Both types are immutable, ensuring referential transparency and safe concurrent operations.
