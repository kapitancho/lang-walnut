<?php

namespace Walnut\Lang\Almond\Source\Implementation\Precompiler;

use Walnut\Lang\Almond\Source\Blueprint\Precompiler\CodePrecompiler;

final readonly class TestPrecompiler implements CodePrecompiler {

	public function determineSourcePath(string $sourcePath): string|null {
		if (!str_ends_with($sourcePath, '-test')) {
			return null;
		}
		return str_replace('-test', '.test.nut', $sourcePath);
	}

	public function precompileSourceCode(string $moduleName, string $sourceCode): string {
		$sourceCode = preg_replace(
			[
				'#^test (.*?) %% (.*?):#',
				'#^test (.*?):#',
			],
			[
				'module $1-test %% $1, $2, \\$test/runner:',
				'module $1-test %% $1, \\$test/runner:',
			],
			$sourceCode
		);
		$sourceCode .= <<<CODE
		
			%% ~TestCases => {
				greenMark = '\033[1;32m';
				redMark = '\033[1;31m';
				endMark = '\033[0m';
				testCases->map(^ ~TestCase => String :: {
					testResult = testCase->invoke;
					before = testResult.before;
					beforeResult = ?whenTypeOf(before) {
						`^Null => Any: before()
					};
					?whenIsError(beforeResult) {
						=> [
							mark: redMark,
							name: testResult.name,
							result: beforeResult->printed,
							end: endMark
						]->format('{mark}ERROR: {name} (before hook failed: {result}){end}')
					};
					actualResult = testResult.actual();
					after = testResult.after;
					afterResult = ?whenTypeOf(after) {
						`^Null => Any: after()
					};
					?whenIsError(afterResult) {
						=> [
							mark: redMark,
							name: testResult.name,
							result: afterResult->printed,
							end: endMark
						]->format('{mark}ERROR: {name} (after hook failed: {result}){end}')
					};
					resultMatches = actualResult == testResult.expected;
					?when(resultMatches) {
						[
							mark: greenMark,
							name: testResult.name,
							end: endMark
						]->format('{mark}PASS: {name}{end}')
					} ~ {
						[
							mark: redMark,
							name: testResult.name,
							expected: testResult.expected->printed,
							actual: actualResult->printed, 
							end: endMark
						]->format('{mark}FAIL: {name} (expected: {expected}, got: {actual}){end}')
					}
				})->combineAsString('\n');
			};
		CODE;
		return $sourceCode;
	}
}