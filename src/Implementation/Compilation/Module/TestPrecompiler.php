<?php

namespace Walnut\Lang\Implementation\Compilation\Module;

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
			['#^test (.*?):#', '#^test (.*?) %% (.*?):#'],
			[
				'module $1-test %% $1, \\$test/runner:',
				'module $1-test %% $1, $2, \\$test/runner:'
			],
			$sourceCode
		);
		$sourceCode .= <<<CODE
		
			main = ^args: Array<String> => String %% [~TestCases] :: {
				%testCases->map(^ ~TestCase => String :: {
					testResult = testCase->invoke;
					actualResult = testResult.actual();
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