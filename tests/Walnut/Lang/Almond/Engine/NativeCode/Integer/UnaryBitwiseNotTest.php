<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Integer;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class UnaryBitwiseNotTest extends CodeExecutionTestHelper {

	public function testUnaryBitwiseNot(): void {
		$result = $this->executeCodeSnippet("~99;");
		$this->assertEquals("9223372036854775708", $result);
	}
}