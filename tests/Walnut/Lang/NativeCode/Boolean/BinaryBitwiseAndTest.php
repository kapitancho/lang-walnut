<?php

namespace Walnut\Lang\NativeCode\Boolean;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class BinaryBitwiseAndTest extends CodeExecutionTestHelper {

	public function testBinaryBitwiseAndFalseFalse(): void {
		$result = $this->executeCodeSnippet("false & false;");
		$this->assertEquals("false", $result);
	}

	public function testBinaryBitwiseAndFalseTrue(): void {
		$result = $this->executeCodeSnippet("false & true;");
		$this->assertEquals("false", $result);
	}

	public function testBinaryBitwiseAndTrueFalse(): void {
		$result = $this->executeCodeSnippet("false & true;");
		$this->assertEquals("false", $result);
	}

	public function testBinaryBitwiseAndTrueTrue(): void {
		$result = $this->executeCodeSnippet("true & true;");
		$this->assertEquals("true", $result);
	}

	public function testBinaryBitwiseAndReturnType(): void {
		$result = $this->executeCodeSnippet(
			"bool[true, false];",
			valueDeclarations: "
				bool = ^[a: Boolean, b: Boolean] => Boolean :: #a & #b;
			"
		);
		$this->assertEquals("false", $result);
	}

	public function testBinaryBitwiseAndReturnTypeTrue(): void {
		$result = $this->executeCodeSnippet(
			"bool[true, false];",
			valueDeclarations: "
				bool = ^[a: Boolean, b: False] => False :: #a & #b;
			"
		);
		$this->assertEquals("false", $result);
	}

	public function testBinaryBitwiseAndInvalidParameterType(): void {
		$this->executeErrorCodeSnippet(
			"Invalid parameter type: Integer[42]",
			"true & 42;");
	}

}