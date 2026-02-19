<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Mutable;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class UnshiftTest extends CodeExecutionTestHelper {

	public function testUnshift(): void {
		$result = $this->executeCodeSnippet("mutable{Array, [1, 2, 3]}->UNSHIFT(5);");
		$this->assertEquals("mutable{Array, [5, 1, 2, 3]}", $result);
	}

	public function testUnshiftInvalidTargetType(): void {
		$this->executeErrorCodeSnippet('The value type of the target must be an unbounded Array type, got Real', "mutable{Real, 3.14}->UNSHIFT(2);");
	}

	public function testUnshiftInvalidParameterType(): void {
		$this->executeErrorCodeSnippet("The parameter type String['hi'] is not a subtype of the item type Integer",
			"mutable{Array<Integer>, [1, 2, 3]}->UNSHIFT('hi');");
	}

}