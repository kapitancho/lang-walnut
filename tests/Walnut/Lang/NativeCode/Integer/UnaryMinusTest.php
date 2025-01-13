<?php

namespace Walnut\Lang\NativeCode\Integer;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class UnaryMinusTest extends CodeExecutionTestHelper {

	public function testUnaryMinusPositive(): void {
		$result = $this->executeCodeSnippet("- {3};");
		$this->assertEquals("-3", $result);
	}

	public function testUnaryMinusNegative(): void {
		$result = $this->executeCodeSnippet("- {-4};");
		$this->assertEquals("4", $result);
	}

	public function testUnaryMinusRange(): void {
		$result = $this->executeCodeSnippet("- {#->length};");
		$this->assertEquals("0", $result);
	}
}