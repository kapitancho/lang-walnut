<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Real;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class FloorTest extends CodeExecutionTestHelper {

	public function testFloorPositive(): void {
		$result = $this->executeCodeSnippet("3.14->floor;");
		$this->assertEquals("3", $result);
	}

	public function testFloorPositiveRounding(): void {
		$result = $this->executeCodeSnippet("3.77->floor;");
		$this->assertEquals("3", $result);
	}

	public function testFloorNegative(): void {
		$result = $this->executeCodeSnippet("-3.14->floor;");
		$this->assertEquals("-4", $result);
	}

	public function testFloorNegativeRounding(): void {
		$result = $this->executeCodeSnippet("-3.77->floor;");
		$this->assertEquals("-4", $result);
	}

	public function testFloorReturnTypeReal(): void {
		$result = $this->executeCodeSnippet(
			"floor(2.77);",
			valueDeclarations: "
				floor = ^r: Real<[-3.5..3.5]> => Integer<[-4..3]> :: r->floor;
			"
		);
		$this->assertEquals("2", $result);
	}

	public function testFloorReturnTypeIntegerClosed(): void {
		$result = $this->executeCodeSnippet(
			"floor(2.77);",
			valueDeclarations: "
				floor = ^r: Real<[-3..3]> => Integer<[-3..3]> :: r->floor;
			"
		);
		$this->assertEquals("2", $result);
	}

	public function testFloorReturnTypeIntegerOpen(): void {
		$result = $this->executeCodeSnippet(
			"floor(2.77);",
			valueDeclarations: "
				floor = ^r: Real<(-3..3)> => Integer<[-3..3)> :: r->floor;
			"
		);
		$this->assertEquals("2", $result);
	}
}