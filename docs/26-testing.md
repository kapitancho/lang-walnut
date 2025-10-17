# 19. Testing

## Overview

Walnut provides a built-in testing framework that allows writing tests in `.test.nut` files. Tests are defined as arrays of test cases that can be executed by the test runner. The framework supports assertions, setup/teardown, and test isolation.

## 19.1 Test File Format

### 19.1.1 File Extension

Test files use the `.test.nut` extension.

**Examples:**
- `user-service.test.nut`
- `math-utils.test.nut`
- `database.test.nut`

### 19.1.2 Test Module Declaration

Test files are declared using the `test` keyword followed by the module path.

**Syntax:** `test $modulePath:`

**Examples:**
```walnut
test $user-service:

test $core/validation:

test $shopping-cart/cart:
```

## 19.2 Test Structure

### 19.2.1 TestCases Type

Tests return a cast to `TestCases`, which is an array of test case functions.

**Type definition:**
```walnut
TestCases = Array<^Null => TestResult>;

TestResult = [
    name: String,
    expected: Any,
    actual: ^Null => Any,
    after: ?^Null => Null
];
```

### 19.2.2 Basic Test Structure

```walnut
test $my-module:

==> TestCases :: [
    /* Test case 1 */
    ^ => TestResult :: TestResult[
        name: 'Test description',
        expected: expectedValue,
        actual: ^ :: actualValue,
        after: ^ :: null  /* Optional cleanup */
    ],

    /* Test case 2 */
    ^ => TestResult :: TestResult[
        name: 'Another test',
        expected: anotherExpectedValue,
        actual: ^ :: anotherActualValue,
        after: ^ :: null
    ]
];
```

## 19.3 TestResult Fields

### 19.3.1 name

**Type:** `String`

**Description:** A human-readable description of what the test verifies.

**Example:**
```walnut
name: 'Addition of two positive integers should return correct sum'
```

### 19.3.2 expected

**Type:** `Any`

**Description:** The expected value that the test should produce.

**Examples:**
```walnut
expected: 42
expected: 'hello world'
expected: [1, 2, 3]
expected: [a: 1, b: 'test']
expected: @'Error message'  /* Expected error */
```

### 19.3.3 actual

**Type:** `^Null => Any`

**Description:** A function that computes the actual value when called. This function is executed by the test runner.

**Examples:**
```walnut
actual: ^ :: 2 + 2

actual: ^ :: {
    x = calculate(10);
    y = transform(x);
    y
}

actual: ^ :: processData(input) => validate
```

### 19.3.4 after

**Type:** `?^Null => Null` (optional)

**Description:** An optional cleanup function that runs after the test, regardless of success or failure. Useful for resetting mutable state or cleaning up resources.

**Examples:**
```walnut
/* No cleanup needed */
after: ^ :: null

/* Reset mutable state */
after: ^ :: {
    counter->SET(0);
    null
}

/* Multiple cleanup operations */
after: ^ :: {
    state->CLEAR;
    buffer->SET('');
    null
}
```

## 19.4 Writing Tests

### 19.4.1 Simple Value Tests

```walnut
test $math:

==> TestCases :: [
    ^ => TestResult :: TestResult[
        name: 'Addition',
        expected: 5,
        actual: ^ :: 2 + 3,
        after: ^ :: null
    ],

    ^ => TestResult :: TestResult[
        name: 'Multiplication',
        expected: 12,
        actual: ^ :: 3 * 4,
        after: ^ :: null
    ]
];
```

### 19.4.2 Function Tests

```walnut
test $string-utils:

capitalize = ^s: String => String ::
    ?when(s->length == 0) {
        s
    } ~ {
        s->substring[start: 0, length: 1]->toUpperCase +
        s->substring[start: 1, length: s->length - 1]->toLowerCase
    };

==> TestCases :: [
    ^ => TestResult :: TestResult[
        name: 'Capitalize empty string',
        expected: '',
        actual: ^ :: capitalize(''),
        after: ^ :: null
    ],

    ^ => TestResult :: TestResult[
        name: 'Capitalize lowercase word',
        expected: 'Hello',
        actual: ^ :: capitalize('hello'),
        after: ^ :: null
    ],

    ^ => TestResult :: TestResult[
        name: 'Capitalize uppercase word',
        expected: 'World',
        actual: ^ :: capitalize('WORLD'),
        after: ^ :: null
    ]
];
```

### 19.4.3 Testing Error Cases

```walnut
test $validator:

validateAge = ^age: Integer => Result<Integer<0..150>, String> ::
    ?when(age < 0 || age > 150) {
        @'Age must be between 0 and 150'
    } ~ {
        age
    };

==> TestCases :: [
    ^ => TestResult :: TestResult[
        name: 'Valid age',
        expected: 25,
        actual: ^ :: validateAge(25),
        after: ^ :: null
    ],

    ^ => TestResult :: TestResult[
        name: 'Negative age returns error',
        expected: @'Age must be between 0 and 150',
        actual: ^ :: validateAge(-5),
        after: ^ :: null
    ],

    ^ => TestResult :: TestResult[
        name: 'Age over 150 returns error',
        expected: @'Age must be between 0 and 150',
        actual: ^ :: validateAge(200),
        after: ^ :: null
    ]
];
```

### 19.4.4 Testing with Mutable State

```walnut
test $counter:

Counter := #[value: Mutable<Integer>];

Counter(initial: Integer) :: Counter[value: mutable{Integer, initial}];

Counter->increment(Null => Integer) ::
    {
        current = $value->value;
        $value->SET(current + 1);
        current + 1
    };

==> TestCases :: {
    /* Shared mutable state for testing */
    testCounter = Counter(0);

    [
        ^ => TestResult :: TestResult[
            name: 'Initial value is 0',
            expected: 0,
            actual: ^ :: testCounter.value->value,
            after: ^ :: null
        ],

        ^ => TestResult :: TestResult[
            name: 'Increment increases value',
            expected: 1,
            actual: ^ :: testCounter->increment,
            after: ^ :: testCounter.value->SET(0)  /* Reset after test */
        ],

        ^ => TestResult :: TestResult[
            name: 'Multiple increments',
            expected: 3,
            actual: ^ :: {
                testCounter->increment;
                testCounter->increment;
                testCounter->increment
            },
            after: ^ :: testCounter.value->SET(0)  /* Reset after test */
        ]
    ]
};
```

### 19.4.5 Testing Collections

```walnut
test $array-utils:

removeDuplicates = ^arr: Array => Array ::
    arr->unique;

==> TestCases :: [
    ^ => TestResult :: TestResult[
        name: 'Empty array',
        expected: [],
        actual: ^ :: removeDuplicates([]),
        after: ^ :: null
    ],

    ^ => TestResult :: TestResult[
        name: 'Array with no duplicates',
        expected: [1, 2, 3],
        actual: ^ :: removeDuplicates([1, 2, 3]),
        after: ^ :: null
    ],

    ^ => TestResult :: TestResult[
        name: 'Array with duplicates',
        expected: [1, 2, 3],
        actual: ^ :: removeDuplicates([1, 2, 2, 3, 1]),
        after: ^ :: null
    ]
];
```

### 19.4.6 Testing Records and Tuples

```walnut
test $data-transform:

transformUser = ^[id: Integer, name: String] => [userId: Integer, userName: String] ::
    [userId: #id, userName: #name];

==> TestCases :: [
    ^ => TestResult :: TestResult[
        name: 'Transform user record',
        expected: [userId: 1, userName: 'Alice'],
        actual: ^ :: transformUser[id: 1, name: 'Alice'],
        after: ^ :: null
    ],

    ^ => TestResult :: TestResult[
        name: 'Transform preserves values',
        expected: [userId: 42, userName: 'Bob'],
        actual: ^ :: transformUser[id: 42, name: 'Bob'],
        after: ^ :: null
    ]
];
```

## 19.5 Advanced Testing Patterns

### 19.5.1 Setup and Teardown with Mutable State

```walnut
test $event:

==> TestCases :: {
    /* Setup: Create mutable state */
    val1 = mutable{String, 'initial value'};
    val2 = mutable{Integer, 0};
    val3 = mutable{String, 'original value'};

    /* Cleanup function */
    afterFn = ^ :: {
        val1->SET('initial value');
        val2->SET(0);
        val3->SET('original value');
    };

    [
        ^ => TestResult :: TestResult[
            name: 'Test EventBus Success',
            expected: ['event triggered', 42, 'original value', [x: 42]],
            actual: ^ :: {
                bus = EventBus[
                    listeners: [
                        ^Any => Null :: { val1->SET('event triggered'); null },
                        ^[x: Integer] => Null :: { val2->SET(#x); null },
                        ^[a: String] => Null :: { val3->SET(#a); null }
                    ]
                ];
                result = bus->fire[x: 42];
                [val1->value, val2->value, val3->value, result]
            },
            after: afterFn
        ],

        ^ => TestResult :: TestResult[
            name: 'Test EventBus Error',
            expected: ['event triggered', 0, 'original value', @ExternalError[
                errorType: 'test error',
                originalError: null,
                errorMessage: 'Test Error'
            ]],
            actual: ^ :: {
                bus = EventBus[
                    listeners: [
                        ^Any => Null :: { val1->SET('event triggered'); null },
                        ^Any => *Null :: {
                            @ExternalError[
                                errorType: 'test error',
                                originalError: null,
                                errorMessage: 'Test Error'
                            ]
                        },
                        ^[x: Integer] => Null :: { val2->SET(#x); null }
                    ]
                ];
                result = bus->fire[x: 42];
                [val1->value, val2->value, val3->value, result]
            },
            after: afterFn
        ]
    ]
};
```

### 19.5.2 Testing with Dependencies

```walnut
test $user-service %% [~MockDatabase]:

/* Mock database for testing */
==> MockDatabase :: MockDb[
    users: mutable{Array<User>, []}
];

findUserById = ^id: Integer => Result<User, String> %% [~MockDatabase] :: {
    users = %mockDatabase.users->value;
    result = users->findFirst(^u => Boolean :: u.id == id);
    ?whenIsError(result) {
        @'User not found'
    } ~ {
        result
    }
};

==> TestCases :: {
    /* Setup test data */
    testUsers = [
        User[id: 1, name: 'Alice'],
        User[id: 2, name: 'Bob']
    ];

    [
        ^ => TestResult :: TestResult[
            name: 'Find existing user',
            expected: User[id: 1, name: 'Alice'],
            actual: ^ :: {
                /* Setup */
                %mockDatabase.users->SET(testUsers);
                /* Test */
                findUserById(1)
            },
            after: ^ :: {
                /* Cleanup */
                %mockDatabase.users->SET([]);
                null
            }
        ],

        ^ => TestResult :: TestResult[
            name: 'Find non-existing user',
            expected: @'User not found',
            actual: ^ :: {
                /* Setup */
                %mockDatabase.users->SET(testUsers);
                /* Test */
                findUserById(999)
            },
            after: ^ :: {
                /* Cleanup */
                %mockDatabase.users->SET([]);
                null
            }
        ]
    ]
};
```

### 19.5.3 Testing Type Conversions

```walnut
test $conversions:

==> TestCases :: [
    ^ => TestResult :: TestResult[
        name: 'String to Integer - valid',
        expected: 42,
        actual: ^ :: '42' => asInteger,
        after: ^ :: null
    ],

    ^ => TestResult :: TestResult[
        name: 'String to Integer - invalid',
        expected: @NotANumber,
        actual: ^ :: 'abc' => asInteger,
        after: ^ :: null
    ],

    ^ => TestResult :: TestResult[
        name: 'Integer to Real',
        expected: 42.0,
        actual: ^ :: 42->asReal,
        after: ^ :: null
    ],

    ^ => TestResult :: TestResult[
        name: 'Boolean to Integer',
        expected: 1,
        actual: ^ :: true->asInteger,
        after: ^ :: null
    ]
];
```

### 19.5.4 Testing Complex Business Logic

```walnut
test $order-service:

Order = [id: Integer, items: Array<OrderItem>, total: Real];
OrderItem = [productId: Integer, quantity: Integer, price: Real];

calculateOrderTotal = ^Order => Real ::
    $.items->map(^item => Real :: #quantity->asReal * #price)->sum;

applyDiscount = ^[order: Order, discountPercent: Real<0..100>] => Real ::
    #order => calculateOrderTotal * (100.0 - #discountPercent) / 100.0;

==> TestCases :: [
    ^ => TestResult :: TestResult[
        name: 'Calculate total for empty order',
        expected: 0.0,
        actual: ^ :: {
            order = Order[id: 1, items: [], total: 0.0];
            calculateOrderTotal(order)
        },
        after: ^ :: null
    ],

    ^ => TestResult :: TestResult[
        name: 'Calculate total with multiple items',
        expected: 35.0,
        actual: ^ :: {
            order = Order[
                id: 1,
                items: [
                    OrderItem[productId: 1, quantity: 2, price: 10.0],
                    OrderItem[productId: 2, quantity: 3, price: 5.0]
                ],
                total: 0.0
            ];
            calculateOrderTotal(order)
        },
        after: ^ :: null
    ],

    ^ => TestResult :: TestResult[
        name: 'Apply 10% discount',
        expected: 31.5,
        actual: ^ :: {
            order = Order[
                id: 1,
                items: [
                    OrderItem[productId: 1, quantity: 2, price: 10.0],
                    OrderItem[productId: 2, quantity: 3, price: 5.0]
                ],
                total: 0.0
            ];
            applyDiscount[order: order, discountPercent: 10.0]
        },
        after: ^ :: null
    ]
];
```

## 19.6 Test Runner

### 19.6.1 Running Tests

Tests are executed by the Walnut test runner, which:
1. Loads test files
2. Executes each test case
3. Compares `expected` and `actual` values
4. Runs `after` cleanup functions
5. Reports results

### 19.6.2 Test Execution

For each test case:
1. The `actual` function is called to compute the result
2. The result is compared with `expected`
3. The `after` function (if present) is called for cleanup
4. The test is marked as passed or failed

### 19.6.3 Value Comparison

The test runner compares values using structural equality:
- Primitives are compared by value
- Collections are compared recursively
- Errors are compared by type and value
- Records and tuples are compared field by field

## 19.7 Best Practices

### 19.7.1 Test Naming

```walnut
/* Good: Descriptive test names */
name: 'Addition of positive integers returns correct sum'
name: 'Division by zero returns NotANumber error'
name: 'Empty array returns empty result'

/* Avoid: Vague names */
/* name: 'Test 1' */
/* name: 'Works' */
```

### 19.7.2 One Assertion Per Test

```walnut
/* Good: Each test verifies one thing */
^ => TestResult :: TestResult[
    name: 'Array length after adding element',
    expected: 4,
    actual: ^ :: [1, 2, 3]->insertLast(4)->length,
    after: ^ :: null
]

/* Avoid: Multiple assertions in one test */
```

### 19.7.3 Test Independence

```walnut
/* Good: Tests don't depend on each other */
[
    ^ => TestResult :: TestResult[
        name: 'Test A',
        expected: result1,
        actual: ^ :: computation1,
        after: ^ :: cleanup1
    ],
    ^ => TestResult :: TestResult[
        name: 'Test B',
        expected: result2,
        actual: ^ :: computation2,
        after: ^ :: cleanup2
    ]
]

/* Each test can run independently */
```

### 19.7.4 Clean Up Side Effects

```walnut
/* Good: Always clean up mutable state */
^ => TestResult :: TestResult[
    name: 'Test with mutable state',
    expected: value,
    actual: ^ :: {
        state->SET(newValue);
        process(state)
    },
    after: ^ :: {
        state->SET(initialValue);  /* Reset */
        null
    }
]
```

### 19.7.5 Test Edge Cases

```walnut
/* Good: Test boundary conditions */
[
    ^ => TestResult :: TestResult[
        name: 'Empty input',
        expected: [],
        actual: ^ :: process([]),
        after: ^ :: null
    ],
    ^ => TestResult :: TestResult[
        name: 'Single element',
        expected: [1],
        actual: ^ :: process([1]),
        after: ^ :: null
    ],
    ^ => TestResult :: TestResult[
        name: 'Large input',
        expected: result,
        actual: ^ :: process(largeArray),
        after: ^ :: null
    ]
]
```

### 19.7.6 Use Helper Functions

```walnut
test $calculator:

/* Helper functions for test setup */
makeTestOrder = ^total: Real => Order ::
    Order[id: 1, items: [], total: total];

makeTestUser = ^name: String => User ::
    User[id: 1, name: name, email: name + '@test.com'];

==> TestCases :: [
    ^ => TestResult :: TestResult[
        name: 'Process order',
        expected: result,
        actual: ^ :: processOrder(makeTestOrder(100.0)),
        after: ^ :: null
    ]
];
```

## Summary

Walnut's testing framework provides:

- **`.test.nut` files** for test definitions
- **TestResult structure** with name, expected, actual, and after fields
- **Flexible assertions** comparing any values
- **Setup and teardown** through after functions
- **Mutable state testing** with proper cleanup
- **Error testing** with Result and Error types
- **Dependency injection** support in tests
- **Test isolation** through independent test cases

Best practices include:
- Write descriptive test names
- Keep tests focused and independent
- Test edge cases and error conditions
- Clean up side effects
- Use helper functions for setup
- Test one thing per test case

This framework enables comprehensive testing of Walnut applications while maintaining type safety and functional programming principles.
