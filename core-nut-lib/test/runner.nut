module $test/runner:

TestResult := #[name: String, expected: Any, actual: ^Null => Any];

TestCase = ^Null => TestResult;
TestCases = Array<TestCase>;