<?php

namespace Walnut\Lang\Test\NativeCode\Mutable;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class PushTest extends CodeExecutionTestHelper {

	public function testPush(): void {
		$result = $this->executeCodeSnippet("mutable{Array, [1, 2, 3]}->PUSH(5);");
		$this->assertEquals("mutable{Array, [1, 2, 3, 5]}", $result);
	}

	public function testPushInvalidTargetType(): void {
		$this->executeErrorCodeSnippet('Invalid target type', "mutable{Real, 3.14}->PUSH(2);");
	}

	public function testPushInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "mutable{Array<Integer>, [1, 2, 3]}->PUSH('hi');");
	}

}