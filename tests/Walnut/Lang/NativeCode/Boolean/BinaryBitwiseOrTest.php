<?php

namespace Walnut\Lang\NativeCode\Boolean;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class BinaryBitwiseOrTest extends CodeExecutionTestHelper {

	public function testBinaryOrFalseFalse(): void {
		$result = $this->executeCodeSnippet("false | false;");
		$this->assertEquals("false", $result);
	}

	public function testBinaryOrFalseTrue(): void {
		$result = $this->executeCodeSnippet("false | true;");
		$this->assertEquals("true", $result);
	}

	public function testBinaryOrTrueFalse(): void {
		$result = $this->executeCodeSnippet("false | true;");
		$this->assertEquals("true", $result);
	}

	public function testBinaryOrTrueTrue(): void {
		$result = $this->executeCodeSnippet("true | true;");
		$this->assertEquals("true", $result);
	}

	public function testBinaryOrReturnType(): void {
		$result = $this->executeCodeSnippet(
			"bool[true, true];",
			valueDeclarations: "
				bool = ^[a: Boolean, b: Boolean] => Boolean :: #a | #b;
			"
		);
		$this->assertEquals("true", $result);
	}

	public function testBinaryOrReturnTypeTrue(): void {
		$result = $this->executeCodeSnippet(
			"bool[true, true];",
			valueDeclarations: "
				bool = ^[a: Boolean, b: True] => True :: #a | #b;
			"
		);
		$this->assertEquals("true", $result);
	}

	public function testBinaryOrInvalidParameterType(): void {
		$this->executeErrorCodeSnippet(
			"Invalid parameter type: Integer[42]",
			"true | 42;");
	}

}