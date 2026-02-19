<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Mutable;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;


final class PopTest extends CodeExecutionTestHelper {

	public function testPopEmpty(): void {
		$result = $this->executeCodeSnippet("mutable{Array, []}->POP;");
		$this->assertEquals("@ItemNotFound", $result);
	}

	public function testPopNotEmpty(): void {
		$result = $this->executeCodeSnippet("mutable{Array, [1, 2, 3]}->POP;");
		$this->assertEquals("3", $result);
	}

	public function testPopInvalidTargetTypeRange(): void {
		$this->executeErrorCodeSnippet('The value type of the target set must be a subtype of Array or Tuple with a minimum length of 0, got Array<2..>', "mutable{Array<2..>, [1, 2, 3]}->POP;");
	}

	public function testPopInvalidTargetType(): void {
		$this->executeErrorCodeSnippet('The value type of the target set must be a subtype of Array or Tuple with a minimum length of 0, got Real',
			"mutable{Real, 3.14}->POP;");
	}

	public function testPopInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type: Integer[2]', "mutable{Array, [1, 2, 3]}->POP(2);");
	}

}