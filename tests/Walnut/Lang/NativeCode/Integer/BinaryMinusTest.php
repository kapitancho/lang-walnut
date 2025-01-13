<?php

namespace Walnut\Lang\NativeCode\Integer;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class BinaryMinusTest extends CodeExecutionTestHelper {

	public function testBinaryMinus(): void {
		$result = $this->executeCodeSnippet("3 - 5;");
		$this->assertEquals("-2", $result);
	}

	public function testBinaryMinusReal(): void {
		$result = $this->executeCodeSnippet("3 - 5.14;");
		$this->assertEquals("-2.14", $result);
	}

	public function testBinaryMinusInvalidParameter(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "3 - 'hello';");
	}

}