<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Integer;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class BinaryBitwiseAndTest extends CodeExecutionTestHelper {

	public function testBinaryBitwiseAnd(): void {
		$result = $this->executeCodeSnippet("3 & 5;");
		$this->assertEquals("1", $result);
	}

	public function testBinaryBitwiseAndInvalidParameter(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "3 & 'hello';");
	}

}