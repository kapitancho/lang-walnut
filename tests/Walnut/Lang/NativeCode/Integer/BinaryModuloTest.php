<?php

namespace Walnut\Lang\Test\NativeCode\Integer;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class BinaryModuloTest extends CodeExecutionTestHelper {

	public function testBinaryModulo(): void {
		$result = $this->executeCodeSnippet("3 % 2;");
		$this->assertEquals("1", $result);
	}

	public function testBinaryModuloZeroInteger(): void {
		$result = $this->executeCodeSnippet("3 % 0;");
		$this->assertEquals("@NotANumber", $result);
	}

	public function testBinaryModuloSubsets(): void {
		$result = $this->executeCodeSnippet(
			"modulo[5, 3];",
			valueDeclarations: "modulo = ^[a: Integer[5, 2], b: Integer[2, 3]] => Integer[0, 1, 2] :: #a % #b;"
		);
		$this->assertEquals("2", $result);
	}

	public function testBinaryModuloSubsetsWithZero(): void {
		$result = $this->executeCodeSnippet(
			"modulo[5, 0];",
			valueDeclarations: "modulo = ^[a: Integer[5, 2], b: Integer[2, 3, 0]] => Result<Integer[0, 1, 2], NotANumber> :: #a % #b;"
		);
		$this->assertEquals("@NotANumber", $result);
	}

	public function testBinaryModuloZeroReal(): void {
		$result = $this->executeCodeSnippet(
			"modulo(3.27 - 3.27);",
			valueDeclarations: "modulo = ^a: Real<0..> => Result<Real, NotANumber> :: 15 % a;"
		);
		$this->assertEquals("@NotANumber", $result);
	}

	public function testBinaryModuloReal(): void {
		$result = $this->executeCodeSnippet("5 % 1.5;");
		$this->assertEquals("0.5", $result);
	}

	public function testBinaryModuloInvalidParameter(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "3 % 'hello';");
	}

}