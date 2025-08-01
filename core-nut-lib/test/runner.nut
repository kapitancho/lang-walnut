module $test/runner:

TestResult := #[
    name: String,
    expected: Any,
    actual: ^Null => Any,
    before: OptionalKey<^Null => Any>,
    after: OptionalKey<^Null => Any>
];

TestCase = ^Null => TestResult;
TestCases = Array<TestCase>;