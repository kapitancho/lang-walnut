<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Boolean;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class UnaryBitwiseNotTest extends CodeExecutionTestHelper {

	public function testUnaryBitwiseNotFalse(): void {
		$result = $this->executeCodeSnippet("~false;");
		$this->assertEquals("true", $result);
	}

	public function testUnaryBitwiseNotTrue(): void {
		$result = $this->executeCodeSnippet("~true;");
		$this->assertEquals("false", $result);
	}

	public function testUnaryNotFalse(): void {
		$result = $this->executeCodeSnippet("!false;");
		$this->assertEquals("true", $result);
	}

	public function testUnaryNotTrue(): void {
		$result = $this->executeCodeSnippet("!true;");
		$this->assertEquals("false", $result);
	}

	public function testUnaryBitwiseNotType(): void {
		$result = $this->executeCodeSnippet("not(true);",
			valueDeclarations: 'not = ^b: Boolean => Boolean :: ~b;'
		);
		$this->assertEquals("false", $result);
	}

	public function testAsIntegerInvalidParameterType(): void {
		$this->executeErrorCodeSnippet(
			"Invalid parameter type",
			"true->unaryBitwiseNot(1);"
		);
	}

}