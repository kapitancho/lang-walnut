<?php

namespace Walnut\Lang\Test\NativeCode\Boolean;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class UnaryNotTest extends CodeExecutionTestHelper {

	public function testUnaryNotTrue(): void {
		$result = $this->executeCodeSnippet("!true;");
		$this->assertEquals("false", $result);
	}

	public function testUnaryNotFalse(): void {
		$result = $this->executeCodeSnippet("!false;");
		$this->assertEquals("true", $result);
	}

	public function testUnaryNotBoolTrue(): void {
		$result = $this->executeCodeSnippet("!1;");
		$this->assertEquals("false", $result);
	}

	public function testUnaryNotBoolFalse(): void {
		$result = $this->executeCodeSnippet("!0;");
		$this->assertEquals("true", $result);
	}

	public function testUnaryNotTwice(): void {
		$result = $this->executeCodeSnippet("!!1;");
		$this->assertEquals("true", $result);
	}

	public function testUnaryNotReturnTypeFalse(): void {
		$result = $this->executeCodeSnippet(
			"not(2);",
			valueDeclarations: "not = ^x: Integer<2..4> => False :: !x;"
		);
		$this->assertEquals("false", $result);
	}

	public function testUnaryNotReturnTypeBoolean(): void {
		$result = $this->executeCodeSnippet(
			"not(2);",
			valueDeclarations: "not = ^x: Integer<0..4> => Boolean :: !x;"
		);
		$this->assertEquals("false", $result);
	}

	public function testUnaryNotReturnTypeTrue(): void {
		$result = $this->executeCodeSnippet(
			"not(0);",
			valueDeclarations: "not = ^x: Integer<0..0> => True :: !x;"
		);
		$this->assertEquals("true", $result);
	}

}