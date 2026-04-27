module $test/runner:

TestResult := #[
    name: String,
    expected: Any,
    actual: ^ => Optional,
    before: Optional<^ => Optional>,
    after: Optional<^ => Optional>
];

TestCase = ^ => TestResult;
TestCases = Array<TestCase>;