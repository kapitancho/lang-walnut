<?php

namespace Walnut\Lang\NativeCode\Real;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class UnaryMinusTest extends CodeExecutionTestHelper {

	public function testUnaryMinusPositive(): void {
		$result = $this->executeCodeSnippet("- {3.14};");
		$this->assertEquals("-3.14", $result);
	}

	public function testUnaryMinusNegative(): void {
		$result = $this->executeCodeSnippet("- {-4.5};");
		$this->assertEquals("4.5", $result);
	}
}