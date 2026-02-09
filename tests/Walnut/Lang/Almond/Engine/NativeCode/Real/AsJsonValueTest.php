<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Real;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class AsJsonValueTest extends CodeExecutionTestHelper {

	public function testAsJsonValue(): void {
		$result = $this->executeCodeSnippet("3.14->asJsonValue;");
		$this->assertEquals("3.14", $result);
	}
}