test $event:

==> TestCases :: {
    val1 = mutable{String, 'initial value'};
    val2 = mutable{Integer, 0};
    val3 = mutable{String, 'original value'};

    afterFn = ^ :: {
        val1->SET('initial value');
        val2->SET(0);
        val3->SET('original value');
    };

    [
        ^ => TestResult :: TestResult[
            name: 'Test EventBus Success',
            expected: ['event triggered', 42, 'original value', [x: 42]],
            actual : ^ :: {
                bus = EventBus[
                    listeners: [
                        ^Any => Null :: { val1->SET('event triggered'); null },
                        ^[x: Integer] => Null :: { val2->SET(#x); null },
                        /* should not be called */
                        ^[a: String] => Null :: { val3->SET(#a); null }
                    ]
                ];
                result = bus->fire[x: 42];
                [val1->value, val2->value, val3->value, result];
            },
            after : afterFn
        ],
        ^ => TestResult :: TestResult[
            name: 'Test EventBus Error',
            expected: ['event triggered', 0, 'original value', @ExternalError[
                errorType: 'test error',
                originalError: null,
                errorMessage: 'Test Error'
            ]],
            actual : ^ :: {
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
                        /* should not be called due to an external error */
                        ^[x: Integer] => Null :: { val2->SET(#x); null },
                        /* should not be called */
                        ^[a: String] => Null :: { val3->SET(#a); null }
                    ]
                ];
                result = bus->fire[x: 42];
                [val1->value, val2->value, val3->value, result];
            },
            after : afterFn
        ]
    ]
};

EventListener = ^Nothing => *Null;
EventBus := $[listeners: Array<EventListener>];
