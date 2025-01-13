<?php

namespace Walnut\Lang\NativeCode\Real;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class BinaryPlusTest extends CodeExecutionTestHelper {

	public function testBinaryPlus(): void {
		$result = $this->executeCodeSnippet("3.14 + 5;");
		$this->assertEquals("8.14", $result);
	}

	public function testBinaryPlusReal(): void {
		$result = $this->executeCodeSnippet("3.14 + 5.14;");
		$this->assertEquals("8.28", $result);
	}

	public function testBinaryPlusInvalidParameter(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "3.14 + 'hello';");
	}

}