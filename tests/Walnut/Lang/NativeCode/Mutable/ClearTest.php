<?php

namespace Walnut\Lang\Test\NativeCode\Mutable;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class ClearTest extends CodeExecutionTestHelper {

	public function testClear(): void {
		$result = $this->executeCodeSnippet("mutable{Set, [1; 2; 3]}->CLEAR;");
		$this->assertEquals("mutable{Set, [;]}", $result);
	}

	public function testClearInvalidTargetType(): void {
		$this->executeErrorCodeSnippet('Invalid target type', "mutable{Real, 3.14}->CLEAR;");
	}

}