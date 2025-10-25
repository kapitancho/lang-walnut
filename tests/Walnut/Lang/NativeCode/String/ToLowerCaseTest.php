<?php

namespace Walnut\Lang\Test\NativeCode\String;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class ToLowerCaseTest extends CodeExecutionTestHelper {

	public function testToLowerCase(): void {
		$result = $this->executeCodeSnippet("'My Name'->toLowerCase;");
		$this->assertEquals("'my name'", $result);
	}

}