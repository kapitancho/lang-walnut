<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\String;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class SubstringRangeTest extends CodeExecutionTestHelper {

	public function testSubstringRangeOk(): void {
		$result = $this->executeCodeSnippet("'hello'->substringRange[start: 1, end: 3];");
		$this->assertEquals("'el'", $result);
	}

	public function testSubstringRangeInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "'hello'->substringRange(5);");
	}

	public function testSubstringRangeInvalidParameterKeys(): void {
		$this->executeErrorCodeSnippet('Expected parameter type to be [start: Integer<0..>, end: Integer<0..>], got [length: Integer[10]].', "'hello'->substringRange[length: 10];");
	}

}