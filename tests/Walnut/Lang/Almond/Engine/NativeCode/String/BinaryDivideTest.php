<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\String;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class BinaryDivideTest extends CodeExecutionTestHelper {

	public function testBinaryDivideOk(): void {
		$result = $this->executeCodeSnippet("'hello' / 2;");
		$this->assertEquals("['he', 'll', 'o']", $result);
	}

	public function testBinaryDivideNoRemainder(): void {
		$result = $this->executeCodeSnippet("'hello!' / 2;");
		$this->assertEquals("['he', 'll', 'o!']", $result);
	}

	public function testBinaryDivideNotNeeded(): void {
		$result = $this->executeCodeSnippet("'hello' / 10;");
		$this->assertEquals("['hello']", $result);
	}

	public function testBinaryDivideReturnType1(): void {
		$result = $this->executeCodeSnippet(
			"c('hello');",
			valueDeclarations: "
				c = ^str: String<4..10> => Array<String<1..2>, 2..5> :: str / 2;
			"
		);
		$this->assertEquals("['he', 'll', 'o']", $result);
	}

	public function testBinaryDivideReturnType2(): void {
		$result = $this->executeCodeSnippet(
			"c(2);",
			valueDeclarations: "
				c = ^size: Integer<2..3> => Array<String<1..3>, 4..6> :: 'hello world' / size;
			"
		);
		$this->assertEquals("['he', 'll', 'o ', 'wo', 'rl', 'd']", $result);
	}

	public function testBinaryDivideReturnTypeInfinity(): void {
		$result = $this->executeCodeSnippet(
			"c(2);",
			valueDeclarations: "
				c = ^size: Integer<2..> => Array<String<1..>, 1..6> :: 'hello world' / size;
			"
		);
		$this->assertEquals("['he', 'll', 'o ', 'wo', 'rl', 'd']", $result);
	}

	public function testBinaryDivideInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "'hello' / false;");
	}

	public function testBinaryDivideInvalidParameterTypeRange(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "'hello' / -3;");
	}

}