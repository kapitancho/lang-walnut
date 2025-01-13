<?php

namespace Walnut\Lang\NativeCode\Integer;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class BinaryIntegerDivideTest extends CodeExecutionTestHelper {

	public function testBinaryIntegerDivide(): void {
		$result = $this->executeCodeSnippet("3 // 2;");
		$this->assertEquals("1", $result);
	}

	public function testBinaryIntegerDivideZero(): void {
		$result = $this->executeCodeSnippet("3 // 0;");
		$this->assertEquals("@NotANumber[]", $result);
	}

	public function testBinaryIntegerDivideInvalidParameter(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "3 // 1.4;");
	}

}