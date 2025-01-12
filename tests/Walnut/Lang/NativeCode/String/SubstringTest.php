<?php

namespace Walnut\Lang\NativeCode\String;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class SubstringTest extends CodeExecutionTestHelper {

	public function testSubstringOk(): void {
		$result = $this->executeCodeSnippet("'hello'->substring[start: 1, length: 2];");
		$this->assertEquals("'el'", $result);
	}

	public function testSubstringInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "'hello'->substring(5);");
	}

	public function testSubstringInvalidParameterKeys(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "'hello'->substring[length: 10];");
	}

}