<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Mutable;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class AsJsonValueTest extends CodeExecutionTestHelper {

	public function testAsJsonValue(): void {
		$result = $this->executeCodeSnippet("mutable{String, 'hello'}->asJsonValue;");
		$this->assertEquals("'hello'", $result);
	}

	public function testAsJsonValueReal(): void {
		$result = $this->executeCodeSnippet("mutable{Real, 3.14}->asJsonValue;");
		$this->assertEquals("3.14", $result);
	}

}