<?php

namespace Walnut\Lang\Test\NativeCode\Boolean;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class BinaryAndTest extends CodeExecutionTestHelper {

	public function testBinaryAndFalseFalse(): void {
		$result = $this->executeCodeSnippet("false && false;");
		$this->assertEquals("false", $result);
	}

	public function testBinaryAndFalseTrue(): void {
		$result = $this->executeCodeSnippet("false && true;");
		$this->assertEquals("false", $result);
	}

	public function testBinaryAndTrueFalse(): void {
		$result = $this->executeCodeSnippet("false && true;");
		$this->assertEquals("false", $result);
	}

	public function testBinaryAndTrueTrue(): void {
		$result = $this->executeCodeSnippet("true && true;");
		$this->assertEquals("true", $result);
	}

	public function testBinaryAndFirstFalse(): void {
		$result = $this->executeCodeSnippet("0 && 1;");
		$this->assertEquals("false", $result);
	}

	public function testBinaryAndSecondFalse(): void {
		$result = $this->executeCodeSnippet("1 && 0;");
		$this->assertEquals("false", $result);
	}

	public function testBinaryAndBothTrue(): void {
		$result = $this->executeCodeSnippet("1 && 1;");
		$this->assertEquals("true", $result);
	}

	public function testBinaryAndReturnTypeFalse(): void {
		$result = $this->executeCodeSnippet(
			"and(0);",
			valueDeclarations: "and = ^x: Integer<0> => False :: x && true;"
		);
		$this->assertEquals("false", $result);
	}

	public function testBinaryAndReturnTypeBoolean(): void {
		$result = $this->executeCodeSnippet(
			"and(0);",
			valueDeclarations: "and = ^x: Integer<0..4> => Boolean :: x && true;"
		);
		$this->assertEquals("false", $result);
	}

	public function testBinaryAndEarlyReturn(): void {
		$result = $this->executeCodeSnippet(
			"and(2);",
			valueDeclarations: "and = ^x: Integer<0..4> => Integer<0..4> :: { => x } && false;"
		);
		$this->assertEquals("2", $result);
	}

}