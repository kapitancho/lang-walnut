<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Mutable;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

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
		$this->executeErrorCodeSnippet('The value type of the target set must be a subtype of Array with a minimum length of 0, got Array<2..>',
			"mutable{Array<2..>, [1, 2, 3]}->SHIFT;");
	}

	public function testShiftInvalidTargetType(): void {
		$this->executeErrorCodeSnippet('The value type of the target set must be a subtype of Array with a minimum length of 0, got Real', "mutable{Real, 3.14}->SHIFT;");
	}

	public function testShiftInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "mutable{Array, [1, 2, 3]}->SHIFT(2);");
	}

}