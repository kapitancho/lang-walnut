<?php

namespace Walnut\Lang\Implementation\Compilation\Module\Precompiler;

use Walnut\Lang\Blueprint\Compilation\Module\CodePrecompiler;

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
		
			%% [~TestCases] => {
				%testCases->map(^ ~TestCase => String :: {
					testResult = testCase->invoke;
					before = testResult.before;
					beforeResult = ?whenTypeOf(before) is {
						`^Null => Any: before()
					};
					?whenIsError(beforeResult) {
						=> '\033[1;31m' + 'ERROR: ' + testResult.name + ' (before hook failed: ' + 
						beforeResult->printed + ')' + '\033[0m'
					};
					actualResult = testResult.actual();
					after = testResult.after;
					afterResult = ?whenTypeOf(after) is {
						`^Null => Any: after()
					};
					?whenIsError(afterResult) {
						=> '\033[1;31m' + 'ERROR: ' + testResult.name + ' (after hook failed: ' + 
						afterResult->printed + ')' + '\033[0m'
					};
					resultMatches = actualResult == testResult.expected;
					?when(resultMatches) {
					    '\033[1;32m' + 'PASS: ' + testResult.name + '\033[0m'
					} ~ {
						'\033[1;31m' + 'FAIL: ' + testResult.name + ' (expected: ' + 
						{testResult.expected}->printed + ', got: ' + 
						{actualResult}->printed + ')' + '\033[0m'
					}
				})->combineAsString('\n');
			};
		CODE;
		return $sourceCode;
	}
}