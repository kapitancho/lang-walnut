module $test/runner:

TestResult := #[
    name: String,
    expected: Any,
    actual: ^ => Any,
    before: ?^ => Any,
    after: ?^ => Any
];

TestCase = ^ => TestResult;
TestCases = Array<TestCase>;