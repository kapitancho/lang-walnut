<?php

namespace Walnut\Lang\Test\NativeCode\Boolean;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class BinaryXorTest extends CodeExecutionTestHelper {

	public function testBinaryXorFalseFalse(): void {
		$result = $this->executeCodeSnippet("false ^^ false;");
		$this->assertEquals("false", $result);
	}

	public function testBinaryXorFalseTrue(): void {
		$result = $this->executeCodeSnippet("false ^^ true;");
		$this->assertEquals("true", $result);
	}

	public function testBinaryXorTrueFalse(): void {
		$result = $this->executeCodeSnippet("false ^^ true;");
		$this->assertEquals("true", $result);
	}

	public function testBinaryXorTrueTrue(): void {
		$result = $this->executeCodeSnippet("true ^^ true;");
		$this->assertEquals("false", $result);
	}

	public function testBinaryXorBoolTrueFalse(): void {
		$result = $this->executeCodeSnippet("1 ^^ 0;");
		$this->assertEquals("true", $result);
	}

	public function testBinaryXorBoolFalseTrue(): void {
		$result = $this->executeCodeSnippet("0 ^^ 1;");
		$this->assertEquals("true", $result);
	}

	public function testBinaryXorBothTrue(): void {
		$result = $this->executeCodeSnippet("1 ^^ 1;");
		$this->assertEquals("false", $result);
	}

	public function testBinaryXorBothFalse(): void {
		$result = $this->executeCodeSnippet("0 ^^ 0;");
		$this->assertEquals("false", $result);
	}

	public function testBinaryAndReturnTypeBoolean(): void {
		$result = $this->executeCodeSnippet(
			"xor(0);",
			valueDeclarations: "xor = ^x: Integer<0..4> => Boolean :: x ^^ true;"
		);
		$this->assertEquals("true", $result);
	}

}