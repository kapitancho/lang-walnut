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
			"c(2);",
			valueDeclarations: "
				c = ^size: Integer<2..3> => String<..2> :: 'hello world' % size;
			"
		);
		$this->assertEquals("'d'", $result);
	}

	public function testBinaryModuloInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "'hello' % false;");
	}

	public function testBinaryModuloInvalidParameterTypeRange(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "'hello' % -3;");
	}

}