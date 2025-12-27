<?php

namespace Walnut\Lang\NativeCode\Boolean;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class BinaryBitwiseXorTest extends CodeExecutionTestHelper {

	public function testBinaryXorFalseFalse(): void {
		$result = $this->executeCodeSnippet("false ^ false;");
		$this->assertEquals("false", $result);
	}

	public function testBinaryXorFalseTrue(): void {
		$result = $this->executeCodeSnippet("false ^ true;");
		$this->assertEquals("true", $result);
	}

	public function testBinaryXorTrueFalse(): void {
		$result = $this->executeCodeSnippet("false ^ true;");
		$this->assertEquals("true", $result);
	}

	public function testBinaryXorTrueTrue(): void {
		$result = $this->executeCodeSnippet("true ^ true;");
		$this->assertEquals("false", $result);
	}

	public function testBinaryXorReturnType(): void {
		$result = $this->executeCodeSnippet(
			"bool[true, true];",
			valueDeclarations: "
				bool = ^[a: Boolean, b: Boolean] => Boolean :: #a ^ #b;
			"
		);
		$this->assertEquals("false", $result);
	}

	public function testBinaryXorReturnTypeDifferent(): void {
		$result = $this->executeCodeSnippet(
			"bool[true, false];",
			valueDeclarations: "
				bool = ^[a: True, b: False] => True :: #a ^ #b;
			"
		);
		$this->assertEquals("true", $result);
	}

	public function testBinaryXorReturnTypeSameTrue(): void {
		$result = $this->executeCodeSnippet(
			"bool[true, true];",
			valueDeclarations: "
				bool = ^[a: True, b: True] => False :: #a ^ #b;
			"
		);
		$this->assertEquals("false", $result);
	}

	public function testBinaryXorReturnTypeSameFalse(): void {
		$result = $this->executeCodeSnippet(
			"bool[false, false];",
			valueDeclarations: "
				bool = ^[a: False, b: False] => False :: #a ^ #b;
			"
		);
		$this->assertEquals("false", $result);
	}

	public function testBinaryXorInvalidParameterType(): void {
		$this->executeErrorCodeSnippet(
			"Invalid parameter type: Integer[42]",
			"true ^ 42;");
	}

}