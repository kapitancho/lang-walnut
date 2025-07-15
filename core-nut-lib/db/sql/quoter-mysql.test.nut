test $db/sql/quoter-mysql:

==> TestCases :: {
    [
        ^ => TestResult :: TestResult[
            name: 'Test MySQL quote regular identifier',
            expected: '`field`',
            actual = ^ %% [~SqlQuoter] :: %sqlQuoter.quoteIdentifier('field')
        ],
        ^ => TestResult :: TestResult[
            name: 'Test MySQL quote special identifier',
            expected: '`fi``eld`',
            actual = ^ %% [~SqlQuoter] :: %sqlQuoter.quoteIdentifier('fi`eld')
        ],
        ^ => TestResult :: TestResult[
            name: 'Test MySQL quote regular string value',
            expected: '\`value\`',
            actual = ^ %% [~SqlQuoter] :: %sqlQuoter.quoteValue('value')
        ],
        ^ => TestResult :: TestResult[
            name: 'Test MySQL quote special string value',
            expected: '\`va\\\`l\\\\ue\`',
            actual = ^ %% [~SqlQuoter] :: %sqlQuoter.quoteValue('va\`l\\ue')
        ],
        ^ => TestResult :: TestResult[
            name: 'Test MySQL quote integer value',
            expected: '42',
            actual = ^ %% [~SqlQuoter] :: %sqlQuoter.quoteValue(42)
        ],
        ^ => TestResult :: TestResult[
            name: 'Test MySQL quote real value',
            expected: '3.14',
            actual = ^ %% [~SqlQuoter] :: %sqlQuoter.quoteValue(3.14)
        ],
        ^ => TestResult :: TestResult[
            name: 'Test MySQL quote true value',
            expected: '1',
            actual = ^ %% [~SqlQuoter] :: %sqlQuoter.quoteValue(true)
        ],
        ^ => TestResult :: TestResult[
            name: 'Test MySQL quote false value',
            expected: '0',
            actual = ^ %% [~SqlQuoter] :: %sqlQuoter.quoteValue(false)
        ],
        ^ => TestResult :: TestResult[
            name: 'Test MySQL quote null value',
            expected: 'NULL',
            actual = ^ %% [~SqlQuoter] :: %sqlQuoter.quoteValue(null)
        ]
    ]
};