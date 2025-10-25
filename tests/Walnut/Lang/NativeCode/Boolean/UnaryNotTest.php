<?php

namespace Walnut\Lang\Test\NativeCode\Boolean;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class UnaryNotTest extends CodeExecutionTestHelper {

	public function testUnaryNotTrue(): void {
		$result = $this->executeCodeSnippet("!true;");
		$this->assertEquals("false", $result);
	}

	public function testUnaryNotFalse(): void {
		$result = $this->executeCodeSnippet("!false;");
		$this->assertEquals("true", $result);
	}

}