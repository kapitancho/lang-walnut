<?php

namespace Walnut\Lang\NativeCode\Mutable;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class ShiftTest extends CodeExecutionTestHelper {

	public function testShiftEmpty(): void {
		$result = $this->executeCodeSnippet("mutable{Array, []}->SHIFT;");
		$this->assertEquals("@ItemNotFound", $result);
	}

	public function testShiftNotEmpty(): void {
		$result = $this->executeCodeSnippet("mutable{Array, [1, 2, 3]}->SHIFT;");
		$this->assertEquals("1", $result);
	}

	public function testShiftInvalidTargetTypeRange(): void {
		$this->executeErrorCodeSnippet('Invalid target type', "mutable{Array<2..>, [1, 2, 3]}->SHIFT(2);");
	}

	public function testShiftInvalidTargetType(): void {
		$this->executeErrorCodeSnippet('Invalid target type', "mutable{Real, 3.14}->SHIFT(2);");
	}

}