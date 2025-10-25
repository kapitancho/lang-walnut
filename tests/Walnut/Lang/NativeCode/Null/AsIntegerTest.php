<?php

namespace Walnut\Lang\Test\NativeCode\Null;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class AsIntegerTest extends CodeExecutionTestHelper {

	public function testAsIntegerOk(): void {
		$result = $this->executeCodeSnippet("null->asInteger;");
		$this->assertEquals("0", $result);
	}

}