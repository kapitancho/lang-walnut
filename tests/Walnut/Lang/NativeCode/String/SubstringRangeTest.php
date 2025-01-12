<?php

namespace Walnut\Lang\NativeCode\String;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class SubstringRangeTest extends CodeExecutionTestHelper {

	public function testSubstringRangeOk(): void {
		$result = $this->executeCodeSnippet("'hello'->substringRange[start: 1, end: 3];");
		$this->assertEquals("'el'", $result);
	}

	public function testSubstringRangeInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "'hello'->substringRange(5);");
	}

	public function testSubstringRangeInvalidParameterKeys(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "'hello'->substringRange[length: 10];");
	}

}