<?php

namespace Walnut\Lang\Test\NativeCode\String;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class LengthTest extends CodeExecutionTestHelper {

	public function testLength(): void {
		$result = $this->executeCodeSnippet("'hello'->length;");
		$this->assertEquals("5", $result);
	}

}