<?php

namespace Walnut\Lang\Test\NativeCode\Real;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class CeilTest extends CodeExecutionTestHelper {

	public function testCeilPositive(): void {
		$result = $this->executeCodeSnippet("3.14->ceil;");
		$this->assertEquals("4", $result);
	}

	public function testCeilPositiveRounding(): void {
		$result = $this->executeCodeSnippet("3.77->ceil;");
		$this->assertEquals("4", $result);
	}

	public function testCeilNegative(): void {
		$result = $this->executeCodeSnippet("-3.14->ceil;");
		$this->assertEquals("-3", $result);
	}

	public function testCeilNegativeRounding(): void {
		$result = $this->executeCodeSnippet("-3.77->ceil;");
		$this->assertEquals("-3", $result);
	}

	public function testCeilReturnTypeReal(): void {
		$result = $this->executeCodeSnippet(
			"ceil(-2.77);",
			valueDeclarations: "
				ceil = ^r: Real<[-3.5..3.5]> => Integer<[-3..4]> :: r->ceil;
			"
		);
		$this->assertEquals("-2", $result);
	}

	public function testCeilReturnTypeIntegerClosed(): void {
		$result = $this->executeCodeSnippet(
			"ceil(-2.77);",
			valueDeclarations: "
				ceil = ^r: Real<[-3..3]> => Integer<[-3..3]> :: r->ceil;
			"
		);
		$this->assertEquals("-2", $result);
	}

	public function testCeilReturnTypeIntegerOpen(): void {
		$result = $this->executeCodeSnippet(
			"ceil(-2.77);",
			valueDeclarations: "
				ceil = ^r: Real<(-3..3)> => Integer<(-3..3]> :: r->ceil;
			"
		);
		$this->assertEquals("-2", $result);
	}
}