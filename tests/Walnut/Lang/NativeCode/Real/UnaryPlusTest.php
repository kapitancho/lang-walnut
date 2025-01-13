<?php

namespace Walnut\Lang\NativeCode\Real;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class UnaryPlusTest extends CodeExecutionTestHelper {

	public function testUnaryPlusPositive(): void {
		$result = $this->executeCodeSnippet("+ {3.14};");
		$this->assertEquals("3.14", $result);
	}

	public function testUnaryPlusNegative(): void {
		$result = $this->executeCodeSnippet("+ {-4.5};");
		$this->assertEquals("-4.5", $result);
	}
}