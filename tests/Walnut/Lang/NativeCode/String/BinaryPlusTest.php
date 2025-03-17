<?php

namespace Walnut\Lang\NativeCode\String;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class BinaryPlusTest extends CodeExecutionTestHelper {

	public function testBinaryPlus(): void {
		$result = $this->executeCodeSnippet("'hello ' + 'world';");
		$this->assertEquals("'hello world'", $result);
	}

	public function testBinaryPlusInvalidParameter(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "'hello' + 23;");
	}

}