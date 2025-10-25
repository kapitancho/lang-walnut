<?php

namespace Walnut\Lang\Test\NativeCode\Integer;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class UnaryPlusTest extends CodeExecutionTestHelper {

	public function testUnaryPlusPositive(): void {
		$result = $this->executeCodeSnippet("+ {3};");
		$this->assertEquals("3", $result);
	}

	public function testUnaryPlusNegative(): void {
		$result = $this->executeCodeSnippet("+ {-4};");
		$this->assertEquals("-4", $result);
	}
}