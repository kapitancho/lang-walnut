<?php

namespace Walnut\Lang\Test\NativeCode\Array;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class PadRightTest extends CodeExecutionTestHelper {

	public function testPadRightEmpty(): void {
		$result = $this->executeCodeSnippet("[]->padRight[value: 1, length: 3];");
		$this->assertEquals("[1, 1, 1]", $result);
	}

	public function testPadRightNonEmpty(): void {
		$result = $this->executeCodeSnippet("['a', 1, 2]->padRight[value: 'b', length: 5];");
		$this->assertEquals("['a', 1, 2, 'b', 'b']", $result);
	}

	public function testPadRightSizeOk(): void {
		$result = $this->executeCodeSnippet("['a', 1, 2]->padRight[value: 'b', length: 2];");
		$this->assertEquals("['a', 1, 2]", $result);
	}

	public function testPadRightInvalidParameterValue(): void {
		$this->executeErrorCodeSnippet("Invalid parameter type",
			"['a', 1, 2]->padRight[value: 'b']");
	}

	public function testPadRightInvalidParameterType(): void {
		$this->executeErrorCodeSnippet("Invalid parameter type",
			"['a', 1, 2]->padRight('b')");
	}
}