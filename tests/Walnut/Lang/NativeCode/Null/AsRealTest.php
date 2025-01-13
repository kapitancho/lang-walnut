<?php

namespace Walnut\Lang\NativeCode\Null;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class AsRealTest extends CodeExecutionTestHelper {

	public function testAsRealOk(): void {
		$result = $this->executeCodeSnippet("null->asReal;");
		$this->assertEquals("0", $result);
	}

}