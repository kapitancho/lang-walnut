<?php

namespace Walnut\Lang\NativeCode\Integer;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class UnaryBitwiseNotTest extends CodeExecutionTestHelper {

	public function testUnaryBitwiseNot(): void {
		$result = $this->executeCodeSnippet("~{99};");
		$this->assertEquals("-100", $result);
	}
}