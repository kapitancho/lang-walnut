<?php

namespace Walnut\Lang\NativeCode\Integer;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class BinaryBitwiseOrTest extends CodeExecutionTestHelper {

	public function testBinaryBitwiseOr(): void {
		$result = $this->executeCodeSnippet("3 | 5;");
		$this->assertEquals("7", $result);
	}

	public function testBinaryBitwiseOrInvalidParameter(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "3 | 'hello';");
	}

}