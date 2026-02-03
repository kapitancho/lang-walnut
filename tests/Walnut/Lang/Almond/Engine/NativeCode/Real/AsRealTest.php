<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Real;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class AsRealTest extends CodeExecutionTestHelper {

	public function testAsReal(): void {
		$result = $this->executeCodeSnippet("3.14->asReal;");
		$this->assertEquals("3.14", $result);
	}

}