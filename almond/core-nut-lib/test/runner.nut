module $test/runner:

TestResult := #[
    name: String,
    expected: Any,
    actual: ^ => Optional,
    before: ?^ => Optional,
    after: ?^ => Optional
];

TestCase = ^ => TestResult;
TestCases = Array<TestCase>;