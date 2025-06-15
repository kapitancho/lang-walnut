<?php

namespace Walnut\Lang\NativeCode\Integer;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class BinaryDivideTest extends CodeExecutionTestHelper {

	public function testBinaryDivide(): void {
		$result = $this->executeCodeSnippet("3 / 2;");
		$this->assertEquals("1.5", $result);
	}

	public function testBinaryDivideReal(): void {
		$result = $this->executeCodeSnippet("3 / 1.5;");
		$this->assertEquals("2", $result);
	}

	public function testBinaryDivideZero(): void {
		$result = $this->executeCodeSnippet("3 / 0;");
		$this->assertEquals("@NotANumber", $result);
	}

	public function testBinaryDivideInvalidParameter(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "3 / 'hello';");
	}

}