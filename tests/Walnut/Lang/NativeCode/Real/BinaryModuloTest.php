<?php

namespace Walnut\Lang\Test\NativeCode\Real;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class BinaryModuloTest extends CodeExecutionTestHelper {

	public function testBinaryModulo(): void {
		$result = $this->executeCodeSnippet("3.5 % 2;");
		$this->assertEquals("1.5", $result);
	}

	public function testBinaryModuloZero(): void {
		$result = $this->executeCodeSnippet("3.5 % 0;");
		$this->assertEquals("@NotANumber", $result);
	}

	public function testBinaryModuloReal(): void {
		$result = $this->executeCodeSnippet("4.5 % 1.5;");
		$this->assertEquals("0", $result);
	}

	public function testBinaryModuloSubsets(): void {
		$result = $this->executeCodeSnippet(
			"modulo[5.4, 3];",
			valueDeclarations: "modulo = ^[a: Real[5.4, 2], b: Integer[2, 3]] => Real[0, 1.4, 2, 2.4] :: #a % #b;"
		);
		$this->assertEquals("2.4", $result);
	}

	public function testBinaryModuloSubsetsWithZero(): void {
		$result = $this->executeCodeSnippet(
			"modulo[5.4, 0];",
			valueDeclarations: "modulo = ^[a: Real[5.4, 2], b: Integer[2, 3, 0]] => Result<Real[0, 1.4, 2, 2.4], NotANumber> :: #a % #b;"
		);
		$this->assertEquals("@NotANumber", $result);
	}

	public function testBinaryModuloInvalidParameter(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "4.3 % 'hello';");
	}

}