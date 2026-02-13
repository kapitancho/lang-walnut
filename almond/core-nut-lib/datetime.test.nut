test $datetime:

==> TestCases :: {
    [
        ^ => TestResult :: TestResult[
            name: 'Valid Date Test',
            expected: '2024-02-29',
            actual : ^ :: {Date[2024, 2, 29]}->asString
        ],
        ^ => TestResult :: TestResult[
            name: 'Valid Date From String Test',
            expected: '2024-02-29',
            actual : ^ :: '2024-02-29'->asDate->asString
        ],
        ^ => TestResult :: TestResult[
            name: 'Valid Date From Json Array Test',
            expected: '2024-02-29',
            actual : ^ :: [2024, 2, 29]->asDate->asString
        ],
        ^ => TestResult :: TestResult[
            name: 'Valid Date From Json Object Test',
            expected: '2024-02-29',
            actual : ^ :: [year: 2024, month: 2, day: 29]->asDate->asString
        ],
        ^ => TestResult :: TestResult[
            name: 'Valid Date As Json Test',
            expected: [year: 2024, month: 2, day: 29],
            actual : ^ :: {Date[2024, 2, 29]}->asJsonValue
        ],
        ^ => TestResult :: TestResult[
            name: 'Invalid Date Test',
            expected: @InvalidDate,
            actual : ^ :: Date[2023, 2, 29]
        ],
        ^ => TestResult :: TestResult[
            name: 'Invalid Date From String Test',
            expected: @InvalidDate,
            actual : ^ :: '2023-02-29'->asDate
        ],
        ^ => TestResult :: TestResult[
            name: 'Invalid Date From Json Array Test',
            expected: @InvalidDate,
            actual : ^ :: [2023, 2, 29]->asDate
        ],
        ^ => TestResult :: TestResult[
            name: 'Invalid Date From Json Object Test',
            expected: @InvalidDate,
            actual : ^ :: [year: 2023, month: 2, day: 29]->asDate
        ],

        ^ => TestResult :: TestResult[
            name: 'Valid Time Test',
            expected: '14:30:00',
            actual : ^ :: {Time[14, 30, 0]}->asString
        ],
        ^ => TestResult :: TestResult[
            name: 'Valid Time From String Test',
            expected: '14:30:00',
            actual : ^ :: '14:30:00'->asTime?->asString
        ],
        ^ => TestResult :: TestResult[
            name: 'Valid Time From Json Array Test',
            expected: '14:30:00',
            actual : ^ :: [14, 30, 0]->asTime->asString
        ],
        ^ => TestResult :: TestResult[
            name: 'Valid Time From Json Object Test',
            expected: '14:30:00',
            actual : ^ :: [hour: 14, minute: 30, second: 0]->asTime->asString
        ],
        ^ => TestResult :: TestResult[
            name: 'Invalid Time From String Test',
            expected: @InvalidTime,
            actual : ^ :: '12:94:05'->asTime
        ],
        ^ => TestResult :: TestResult[
            name: 'Invalid Time From Json Array Test',
            expected: @InvalidTime,
            actual : ^ :: [12, 94, 5]->asTime
        ],
        ^ => TestResult :: TestResult[
            name: 'Invalid Time From Json Object Test',
            expected: @InvalidTime,
            actual : ^ :: [hour: 12, minute: 94, second: 5]->asTime
        ],

        ^ => TestResult :: TestResult[
            name: 'Valid DateAndTime Test',
            expected: '2024-02-29 14:30:00',
            actual : ^ :: {DateAndTime[Date[2024, 2, 29]?, Time[14, 30, 0]]}->asString
        ],
        ^ => TestResult :: TestResult[
            name: 'Valid DateAndTime From String Test',
            expected: '2024-02-29 14:30:00',
            actual : ^ :: '2024-02-29 14:30:00'->asDateAndTime->asString
        ],
        ^ => TestResult :: TestResult[
            name: 'Valid DateAndTime From Json Array Test',
            expected: '2024-02-29 14:30:00',
            actual : ^ :: [2024, 2, 29, 14, 30, 0]->asDateAndTime->asString
        ],
        ^ => TestResult :: TestResult[
            name: 'Valid DateAndTime From Json Object Test',
            expected: '2024-02-29 14:30:00',
            actual : ^ :: [year: 2024, month: 2, day: 29, hour: 14, minute: 30, second: 0]->asDateAndTime->asString
        ],
        ^ => TestResult :: TestResult[
            name: 'Valid DateAndTime As Json Test',
            expected: [
                date: [year: 2024, month: 2, day: 29],
                time: [hour: 14, minute: 30, second: 0]
            ],
            actual : ^ :: {DateAndTime[Date[2024, 2, 29]?, Time[14, 30, 0]]}->asJsonValue
        ],
        ^ => TestResult :: TestResult[
            name: 'Invalid DateAndTime Date From String Test',
            expected: @InvalidDate,
            actual : ^ :: '2023-02-29 14:30:00'->asDateAndTime
        ],
        ^ => TestResult :: TestResult[
            name: 'Invalid DateAndTime Time From String Test',
            expected: @InvalidTime,
            actual : ^ :: '2024-02-29 12:94:05'->asDateAndTime
        ],
        ^ => TestResult :: TestResult[
            name: 'Invalid DateAndTime Date From Json Array Test',
            expected: @InvalidDate,
            actual : ^ :: [2023, 2, 29, 14, 30, 0]->asDateAndTime
        ],
        ^ => TestResult :: TestResult[
            name: 'Invalid DateAndTime Time From Json Array Test',
            expected: @InvalidDateAndTime,
            actual : ^ :: [2024, 2, 29, 12, 94, 5]->asDateAndTime
        ],
        ^ => TestResult :: TestResult[
            name: 'Invalid DateAndTime From Json Object Test',
            expected: @InvalidDateAndTime,
            actual : ^ :: [year: 2023, month: 2, day: 29, hour: 12, minute: 94, second: 5]->asDateAndTime
        ]
    ]
};