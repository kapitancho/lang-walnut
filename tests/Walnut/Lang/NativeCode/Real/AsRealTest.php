<?php

namespace Walnut\Lang\Test\NativeCode\Real;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class AsRealTest extends CodeExecutionTestHelper {

	public function testAsReal(): void {
		$result = $this->executeCodeSnippet("3.14->asReal;");
		$this->assertEquals("3.14", $result);
	}

}