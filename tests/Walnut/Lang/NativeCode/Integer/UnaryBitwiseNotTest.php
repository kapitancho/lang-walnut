<?php

namespace Walnut\Lang\Test\NativeCode\Integer;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class UnaryBitwiseNotTest extends CodeExecutionTestHelper {

	public function testUnaryBitwiseNot(): void {
		$result = $this->executeCodeSnippet("~99;");
		$this->assertEquals("9223372036854775708", $result);
	}
}