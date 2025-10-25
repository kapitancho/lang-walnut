<?php

namespace Walnut\Lang\Test\NativeCode\String;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class PadLeftTest extends CodeExecutionTestHelper {

	public function testPadLeftOk(): void {
		$result = $this->executeCodeSnippet("'hello'->padLeft[length: 10, padString: '-'];");
		$this->assertEquals("'-----hello'", $result);
	}

	public function testPadLeftNotNeeded(): void {
		$result = $this->executeCodeSnippet("'hello'->padLeft[length: 4, padString: '-'];");
		$this->assertEquals("'hello'", $result);
	}

	public function testPadLeftInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "'hello'->padLeft(5);");
	}

	public function testPadLeftInvalidParameterKeys(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "'hello'->padLeft[length: 10];");
	}

}