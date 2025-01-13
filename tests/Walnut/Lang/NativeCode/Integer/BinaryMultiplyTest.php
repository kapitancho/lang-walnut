<?php

namespace Walnut\Lang\NativeCode\Integer;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class BinaryMultiplyTest extends CodeExecutionTestHelper {

	public function testBinaryMultiply(): void {
		$result = $this->executeCodeSnippet("3 * 5;");
		$this->assertEquals("15", $result);
	}

	public function testBinaryMultiplyReal(): void {
		$result = $this->executeCodeSnippet("3 * 5.14;");
		$this->assertEquals("15.42", $result);
	}

	public function testBinaryMultiplyInvalidParameter(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "3 * 'hello';");
	}

}