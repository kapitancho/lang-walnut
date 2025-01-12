<?php

namespace Walnut\Lang\NativeCode\String;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class ReverseTest extends CodeExecutionTestHelper {

	public function testReverse(): void {
		$result = $this->executeCodeSnippet("'hello'->reverse;");
		$this->assertEquals("'olleh'", $result);
	}

}