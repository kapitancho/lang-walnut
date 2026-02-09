<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Integer;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class AsJsonValueTest extends CodeExecutionTestHelper {

	public function testAsJsonValue(): void {
		$result = $this->executeCodeSnippet("3->asJsonValue;");
		$this->assertEquals("3", $result);
	}
}