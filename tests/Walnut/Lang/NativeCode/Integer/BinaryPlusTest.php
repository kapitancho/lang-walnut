<?php

namespace Walnut\Lang\NativeCode\Integer;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class BinaryPlusTest extends CodeExecutionTestHelper {

	public function testBinaryPlus(): void {
		$result = $this->executeCodeSnippet("3 + 5;");
		$this->assertEquals("8", $result);
	}

	public function testBinaryPlusReal(): void {
		$result = $this->executeCodeSnippet("3 + 5.14;");
		$this->assertEquals("8.14", $result);
	}

	public function testBinaryPlusInvalidParameter(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "3 + 'hello';");
	}

}