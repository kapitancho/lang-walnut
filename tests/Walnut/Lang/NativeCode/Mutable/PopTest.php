<?php

namespace Walnut\Lang\NativeCode\Mutable;

use Walnut\Lang\Test\CodeExecutionTestHelper;


final class PopTest extends CodeExecutionTestHelper {

	public function testPopEmpty(): void {
		$result = $this->executeCodeSnippet("mutable{Array, []}->POP;");
		$this->assertEquals("@ItemNotFound[]", $result);
	}

	public function testPopNotEmpty(): void {
		$result = $this->executeCodeSnippet("mutable{Array, [1, 2, 3]}->POP;");
		$this->assertEquals("3", $result);
	}

	public function testPopInvalidTargetTypeRange(): void {
		$this->executeErrorCodeSnippet('Invalid target type', "mutable{Array<2..>, [1, 2, 3]}->POP(2);");
	}

	public function testPopInvalidTargetType(): void {
		$this->executeErrorCodeSnippet('Invalid target type', "mutable{Real, 3.14}->POP(2);");
	}

}