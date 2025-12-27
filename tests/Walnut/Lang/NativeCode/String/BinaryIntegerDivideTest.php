<?php

namespace Walnut\Lang\NativeCode\String;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class BinaryIntegerDivideTest extends CodeExecutionTestHelper {

	public function testBinaryIntegerDivideOk(): void {
		$result = $this->executeCodeSnippet("'hello' // 2;");
		$this->assertEquals("['he', 'll']", $result);
	}

	public function testBinaryIntegerDivideNoRemainder(): void {
		$result = $this->executeCodeSnippet("'hello!' // 2;");
		$this->assertEquals("['he', 'll', 'o!']", $result);
	}

	public function testBinaryIntegerDivideNotNeeded(): void {
		$result = $this->executeCodeSnippet("'hello' // 10;");
		$this->assertEquals("[]", $result);
	}

	public function testBinaryIntegerDivideReturnType1(): void {
		$result = $this->executeCodeSnippet(
			"c('hello');",
			valueDeclarations: "
				c = ^str: String<4..10> => Array<String<2>, 2..5> :: str // 2;
			"
		);
		$this->assertEquals("['he', 'll']", $result);
	}

	public function testBinaryIntegerDivideReturnType2(): void {
		$result = $this->executeCodeSnippet(
			"c(2);",
			valueDeclarations: "
				c = ^size: Integer<2..3> => Array<String<2..3>, 3..5> :: 'hello world' // size;
			"
		);
		$this->assertEquals("['he', 'll', 'o ', 'wo', 'rl']", $result);
	}

	public function testBinaryIntegerDivideInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "'hello' // false;");
	}

	public function testBinaryIntegerDivideInvalidParameterTypeRange(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "'hello' // -3;");
	}

}