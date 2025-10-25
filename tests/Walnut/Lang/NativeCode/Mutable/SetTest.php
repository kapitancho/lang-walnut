<?php

namespace Walnut\Lang\Test\NativeCode\Mutable;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class SetTest extends CodeExecutionTestHelper {

	public function testSet(): void {
		$result = $this->executeCodeSnippet("mutable{Real, 3.14}->SET(8);");
		$this->assertEquals("mutable{Real, 8}", $result);
	}

	public function testSetInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "mutable{Real, 3.14}->SET('a');");
	}

}