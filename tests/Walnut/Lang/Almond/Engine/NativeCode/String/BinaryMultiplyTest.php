<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\String;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class BinaryMultiplyTest extends CodeExecutionTestHelper {

	public function testBinaryMultiplyOk(): void {
		$result = $this->executeCodeSnippet("'hello ' * 3;");
		$this->assertEquals("'hello hello hello '", $result);
	}

	public function testBinaryMultiplyInfinity(): void {
		$result = $this->executeCodeSnippet(
			"mul3('hello ');",
			valueDeclarations: "mul3 = ^str: String => String :: str * 3;"
		);
		$this->assertEquals("'hello hello hello '", $result);
	}

	public function testBinaryMultiplyZero(): void {
		$result = $this->executeCodeSnippet("'hello ' * 0;");
		$this->assertEquals("''", $result);
	}

	public function testBinaryMultiplyInvalidParameterValue(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "'hello ' * {-3};");
	}

	public function testBinaryMultiplyInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "'hello ' * false;");
	}

}