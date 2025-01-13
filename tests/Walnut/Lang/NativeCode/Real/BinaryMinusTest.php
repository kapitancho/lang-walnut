<?php

namespace Walnut\Lang\NativeCode\Real;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class BinaryMinusTest extends CodeExecutionTestHelper {

	public function testBinaryMinus(): void {
		$result = $this->executeCodeSnippet("3.5 - 5;");
		$this->assertEquals("-1.5", $result);
	}

	public function testBinaryMinusReal(): void {
		$result = $this->executeCodeSnippet("3.14 - 5.14;");
		$this->assertEquals("-2.00", $result);
	}

	public function testBinaryMinusInvalidParameter(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "3.14 - 'hello';");
	}

}