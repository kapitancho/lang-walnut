<?php

namespace Walnut\Lang\NativeCode\Boolean;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class UnaryBitwiseNotTest extends CodeExecutionTestHelper {

	public function testUnaryNotTrue(): void {
		$result = $this->executeCodeSnippet("~true;");
		$this->assertEquals("false", $result);
	}

	public function testUnaryNotFalse(): void {
		$result = $this->executeCodeSnippet("~false;");
		$this->assertEquals("true", $result);
	}

	public function testUnaryNotReturnType(): void {
		$result = $this->executeCodeSnippet(
			"not(false);",
			valueDeclarations: "not = ^b: Boolean => Boolean :: ~b;"
		);
		$this->assertEquals("true", $result);
	}

	public function testUnaryNotReturnTypeTrue(): void {
		$result = $this->executeCodeSnippet(
			"not(true);",
			valueDeclarations: "not = ^b: True => False :: ~b;"
		);
		$this->assertEquals("false", $result);
	}

	public function testUnaryNotReturnTypeFalse(): void {
		$result = $this->executeCodeSnippet(
			"not(false);",
			valueDeclarations: "not = ^b: False => True :: ~b;"
		);
		$this->assertEquals("true", $result);
	}

}