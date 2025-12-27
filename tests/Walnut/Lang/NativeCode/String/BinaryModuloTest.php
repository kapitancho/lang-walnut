<?php

namespace Walnut\Lang\NativeCode\String;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class BinaryModuloTest extends CodeExecutionTestHelper {

	public function testBinaryModuloOk(): void {
		$result = $this->executeCodeSnippet("'hello' % 2;");
		$this->assertEquals("'o'", $result);
	}

	public function testBinaryModuloNoRemainder(): void {
		$result = $this->executeCodeSnippet("'hello!' % 2;");
		$this->assertEquals("''", $result);
	}

	public function testBinaryModuloNotNeeded(): void {
		$result = $this->executeCodeSnippet("'hello' % 10;");
		$this->assertEquals("'hello'", $result);
	}

	public function testBinaryModuloReturnType1(): void {
		$result = $this->executeCodeSnippet(
			"c('hello');",
			valueDeclarations: "
				c = ^str: String<4..10> => String<..1> :: str % 2;
			"
		);
		$this->assertEquals("'o'", $result);
	}

	public function testBinaryModuloReturnType2(): void {
		$result = $this->executeCodeSnippet(
			"c(5);",
			valueDeclarations: "
				c = ^size: Integer<4..6> => String<..5> :: 'hello world' % size;
			"
		);
		$this->assertEquals("'d'", $result);
	}

	public function testBinaryModuloReturnType3(): void {
		$result = $this->executeCodeSnippet(
			"c('hello');",
			valueDeclarations: "
				c = ^str: String<4..10> => String<4..10> :: str % 12;
			"
		);
		$this->assertEquals("'hello'", $result);
	}


	// Type inference tests
	public function testBinaryModuloTypeBasic(): void {
		$result = $this->executeCodeSnippet(
			"chunk['12345', 2]",
			valueDeclarations: "chunk = ^[str: String<5>, size: Integer<2>] => String<1> :: #str % #size;"
		);
		$this->assertEquals("'5'", $result);
	}

	public function testBinaryModuloTypeMaxInfinityBoth(): void {
		$result = $this->executeCodeSnippet(
			"chunk['12345', 2]",
			valueDeclarations: "chunk = ^[str: String<5..>, size: Integer<2..>] => String :: #str % #size;"
		);
		$this->assertEquals("'5'", $result);
	}

	public function testBinaryModuloTypeMaxInfinity(): void {
		$result = $this->executeCodeSnippet(
			"chunk['12345', 2]",
			valueDeclarations: "chunk = ^[str: String<5>, size: Integer<2..>] => String<..5> :: #str % #size;"
		);
		$this->assertEquals("'5'", $result);
	}

	public function testBinaryModuloTypeMaxInfinityEmptyArray(): void {
		$result = $this->executeCodeSnippet(
			"chunk['12345', 2]",
			valueDeclarations: "chunk = ^[str: String<..5>, size: Integer<2..>] => String<..5> :: #str % #size;"
		);
		$this->assertEquals("'5'", $result);
	}

	public function testBinaryModuloBeyondRange(): void {
		$result = $this->executeCodeSnippet(
			"chunk['12345', 13]",
			valueDeclarations: "chunk = ^[str: String<3..5>, size: Integer<12..14>] => String<3..5> :: #str % #size;"
		);
		$this->assertEquals("'12345'", $result);
	}

	public function testBinaryModuloTypeExactDivision(): void {
		$result = $this->executeCodeSnippet(
			"chunk['123456', 3]",
			valueDeclarations: "chunk = ^[str: String<6>, size: Integer<3>] => String<0> :: #str % #size;"
		);
		$this->assertEquals("''", $result);
	}

	public function testBinaryModuloTypeEmptyArray(): void {
		$result = $this->executeCodeSnippet(
			"chunk['', 2]",
			valueDeclarations: "chunk = ^[str: String<0>, size: Integer<2>] => String<0> :: #str % #size;"
		);
		$this->assertEquals("''", $result);
	}

	public function testBinaryModuloInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "'hello' % false;");
	}

	public function testBinaryModuloInvalidParameterTypeRange(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "'hello' % -3;");
	}

}