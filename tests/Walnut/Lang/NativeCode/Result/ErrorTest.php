<?php

namespace Walnut\Lang\Test\NativeCode\Result;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class ErrorTest extends CodeExecutionTestHelper {

	public function testAsIntegerOk(): void {
		$result = $this->executeCodeSnippet("{@'error'}->error;");
		$this->assertEquals("'error'", $result);
	}

}