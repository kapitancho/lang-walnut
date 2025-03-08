<?php

namespace Walnut\Lang\Implementation\Compilation\Module;

use Walnut\Lang\Blueprint\Compilation\Module\CodePrecompiler;

final readonly class TestPrecompiler implements CodePrecompiler {
	public function precompileSourceCode(string $moduleName, string $sourceCode): string {
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