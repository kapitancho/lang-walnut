<?php

namespace Walnut\Lang\Test\NativeCode\Array;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class PadLeftTest extends CodeExecutionTestHelper {

	public function testPadLeftEmpty(): void {
		$result = $this->executeCodeSnippet("[]->padLeft[value: 1, length: 3];");
		$this->assertEquals("[1, 1, 1]", $result);
	}

	public function testPadLeftNonEmpty(): void {
		$result = $this->executeCodeSnippet("['a', 1, 2]->padLeft[value: 'b', length: 5];");
		$this->assertEquals("['b', 'b', 'a', 1, 2]", $result);
	}

	public function testPadLeftSizeOk(): void {
		$result = $this->executeCodeSnippet("['a', 1, 2]->padLeft[value: 'b', length: 2];");
		$this->assertEquals("['a', 1, 2]", $result);
	}

	public function testPadLeftInvalidParameterValue(): void {
		$this->executeErrorCodeSnippet("Invalid parameter type",
			"['a', 1, 2]->padLeft[value: 'b']");
	}

	public function testPadLeftInvalidParameterType(): void {
		$this->executeErrorCodeSnippet("Invalid parameter type",
			"['a', 1, 2]->padLeft('b')");
	}
}