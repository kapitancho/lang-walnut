<?php

namespace Walnut\Lang\NativeCode\Real;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class BinaryModuloTest extends CodeExecutionTestHelper {

	public function testBinaryModulo(): void {
		$result = $this->executeCodeSnippet("3.5 % 2;");
		$this->assertEquals("1.5", $result);
	}

	public function testBinaryModuloZero(): void {
		$result = $this->executeCodeSnippet("3.5 % 0;");
		$this->assertEquals("@NotANumber()", $result);
	}

	public function testBinaryModuloReal(): void {
		$result = $this->executeCodeSnippet("4.5 % 1.5;");
		$this->assertEquals("0", $result);
	}

	public function testBinaryModuloInvalidParameter(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "4.3 % 'hello';");
	}

}