<?php

namespace Walnut\Lang\Test\NativeCode\Integer;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class BinaryBitwiseXorTest extends CodeExecutionTestHelper {

	public function testBinaryBitwiseXor(): void {
		$result = $this->executeCodeSnippet("3 ^ 5;");
		$this->assertEquals("6", $result);
	}

	public function testBinaryBitwiseXorInvalidParameter(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "3 ^ 'hello';");
	}

}