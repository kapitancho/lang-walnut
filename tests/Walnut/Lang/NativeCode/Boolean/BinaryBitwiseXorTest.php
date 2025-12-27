<?php

namespace Walnut\Lang\NativeCode\Boolean;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class BinaryBitwiseXorTest extends CodeExecutionTestHelper {

	public function testBinaryBitwiseXorFalseFalse(): void {
		$result = $this->executeCodeSnippet("false ^ false;");
		$this->assertEquals("false", $result);
	}

	public function testBinaryBitwiseXorFalseTrue(): void {
		$result = $this->executeCodeSnippet("false ^ true;");
		$this->assertEquals("true", $result);
	}

	public function testBinaryBitwiseXorTrueFalse(): void {
		$result = $this->executeCodeSnippet("false ^ true;");
		$this->assertEquals("true", $result);
	}

	public function testBinaryBitwiseXorTrueTrue(): void {
		$result = $this->executeCodeSnippet("true ^ true;");
		$this->assertEquals("false", $result);
	}

	public function testBinaryBitwiseXorReturnType(): void {
		$result = $this->executeCodeSnippet(
			"bool[true, true];",
			valueDeclarations: "
				bool = ^[a: Boolean, b: Boolean] => Boolean :: #a ^ #b;
			"
		);
		$this->assertEquals("false", $result);
	}

	public function testBinaryBitwiseXorReturnTypeDifferent(): void {
		$result = $this->executeCodeSnippet(
			"bool[true, false];",
			valueDeclarations: "
				bool = ^[a: True, b: False] => True :: #a ^ #b;
			"
		);
		$this->assertEquals("true", $result);
	}

	public function testBinaryBitwiseXorReturnTypeSameTrue(): void {
		$result = $this->executeCodeSnippet(
			"bool[true, true];",
			valueDeclarations: "
				bool = ^[a: True, b: True] => False :: #a ^ #b;
			"
		);
		$this->assertEquals("false", $result);
	}

	public function testBinaryBitwiseXorReturnTypeSameFalse(): void {
		$result = $this->executeCodeSnippet(
			"bool[false, false];",
			valueDeclarations: "
				bool = ^[a: False, b: False] => False :: #a ^ #b;
			"
		);
		$this->assertEquals("false", $result);
	}

	public function testBinaryBitwiseXorInvalidParameterType(): void {
		$this->executeErrorCodeSnippet(
			"Invalid parameter type: Integer[42]",
			"true ^ 42;");
	}

}