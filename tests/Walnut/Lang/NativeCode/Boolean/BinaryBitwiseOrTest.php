<?php

namespace Walnut\Lang\NativeCode\Boolean;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class BinaryBitwiseOrTest extends CodeExecutionTestHelper {

	public function testBinaryBitwiseOrFalseFalse(): void {
		$result = $this->executeCodeSnippet("false | false;");
		$this->assertEquals("false", $result);
	}

	public function testBinaryBitwiseOrFalseTrue(): void {
		$result = $this->executeCodeSnippet("false | true;");
		$this->assertEquals("true", $result);
	}

	public function testBinaryBitwiseOrTrueFalse(): void {
		$result = $this->executeCodeSnippet("false | true;");
		$this->assertEquals("true", $result);
	}

	public function testBinaryBitwiseOrTrueTrue(): void {
		$result = $this->executeCodeSnippet("true | true;");
		$this->assertEquals("true", $result);
	}

	public function testBinaryBitwiseOrReturnType(): void {
		$result = $this->executeCodeSnippet(
			"bool[true, true];",
			valueDeclarations: "
				bool = ^[a: Boolean, b: Boolean] => Boolean :: #a | #b;
			"
		);
		$this->assertEquals("true", $result);
	}

	public function testBinaryBitwiseOrReturnTypeTrue(): void {
		$result = $this->executeCodeSnippet(
			"bool[true, true];",
			valueDeclarations: "
				bool = ^[a: Boolean, b: True] => True :: #a | #b;
			"
		);
		$this->assertEquals("true", $result);
	}

	public function testBinaryBitwiseOrInvalidParameterType(): void {
		$this->executeErrorCodeSnippet(
			"Invalid parameter type: Integer[42]",
			"true | 42;");
	}

}