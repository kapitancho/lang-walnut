<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\True;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class UnaryBitwiseNotTest extends CodeExecutionTestHelper {

	public function testUnaryBitwiseNotFalse(): void {
		$result = $this->executeCodeSnippet("~false;");
		$this->assertEquals("true", $result);
	}

	public function testUnaryNotFalse(): void {
		$result = $this->executeCodeSnippet("!false;");
		$this->assertEquals("true", $result);
	}

	public function testUnaryBitwiseNotType(): void {
		$result = $this->executeCodeSnippet("not(false);",
			valueDeclarations: 'not = ^b: False => True :: ~b;'
		);
		$this->assertEquals("true", $result);
	}

	public function testAsIntegerInvalidParameterType(): void {
		$this->executeErrorCodeSnippet(
			"Invalid parameter type",
			"false->unaryBitwiseNot(1);"
		);
	}

}