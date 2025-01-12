<?php

namespace Walnut\Lang\NativeCode\String;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class BinaryMultiplyTest extends CodeExecutionTestHelper {

	public function testBinaryMultiplyOk(): void {
		$result = $this->executeCodeSnippet("'hello ' * 3;");
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