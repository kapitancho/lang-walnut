<?php

namespace Walnut\Lang\NativeCode\Boolean;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class BinaryBitwiseAndTest extends CodeExecutionTestHelper {

	public function testBinaryAndFalseFalse(): void {
		$result = $this->executeCodeSnippet("false & false;");
		$this->assertEquals("false", $result);
	}

	public function testBinaryAndFalseTrue(): void {
		$result = $this->executeCodeSnippet("false & true;");
		$this->assertEquals("false", $result);
	}

	public function testBinaryAndTrueFalse(): void {
		$result = $this->executeCodeSnippet("false & true;");
		$this->assertEquals("false", $result);
	}

	public function testBinaryAndTrueTrue(): void {
		$result = $this->executeCodeSnippet("true & true;");
		$this->assertEquals("true", $result);
	}

	public function testBinaryAndReturnType(): void {
		$result = $this->executeCodeSnippet(
			"bool[true, false];",
			valueDeclarations: "
				bool = ^[a: Boolean, b: Boolean] => Boolean :: #a & #b;
			"
		);
		$this->assertEquals("false", $result);
	}

	public function testBinaryOrReturnTypeTrue(): void {
		$result = $this->executeCodeSnippet(
			"bool[true, false];",
			valueDeclarations: "
				bool = ^[a: Boolean, b: False] => False :: #a & #b;
			"
		);
		$this->assertEquals("false", $result);
	}

	public function testBinaryAndInvalidParameterType(): void {
		$this->executeErrorCodeSnippet(
			"Invalid parameter type: Integer[42]",
			"true & 42;");
	}

}