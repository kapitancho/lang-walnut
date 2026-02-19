<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Mutable;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class SetTest extends CodeExecutionTestHelper {

	public function testSet(): void {
		$result = $this->executeCodeSnippet("mutable{Real, 3.14}->SET(8);");
		$this->assertEquals("mutable{Real, 8}", $result);
	}

	public function testSetInvalidParameterType(): void {
		$this->executeErrorCodeSnippet("The parameter type String['a'] is not a subtype of the value type Real",
			"mutable{Real, 3.14}->SET('a');");
	}

}