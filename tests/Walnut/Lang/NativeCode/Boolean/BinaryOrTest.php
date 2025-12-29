<?php

namespace Walnut\Lang\Test\NativeCode\Boolean;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class BinaryOrTest extends CodeExecutionTestHelper {

	public function testBinaryOrFalseFalse(): void {
		$result = $this->executeCodeSnippet("false || false;");
		$this->assertEquals("false", $result);
	}

	public function testBinaryOrFalseTrue(): void {
		$result = $this->executeCodeSnippet("false || true;");
		$this->assertEquals("true", $result);
	}

	public function testBinaryOrTrueFalse(): void {
		$result = $this->executeCodeSnippet("false || true;");
		$this->assertEquals("true", $result);
	}

	public function testBinaryOrTrueTrue(): void {
		$result = $this->executeCodeSnippet("true || true;");
		$this->assertEquals("true", $result);
	}

	public function testBinaryOrFirstTrue(): void {
		$result = $this->executeCodeSnippet("1 || 0;");
		$this->assertEquals("true", $result);
	}

	public function testBinaryOrSecondTrue(): void {
		$result = $this->executeCodeSnippet("0 || 1;");
		$this->assertEquals("true", $result);
	}

	public function testBinaryOrBothFalse(): void {
		$result = $this->executeCodeSnippet("0 || 0;");
		$this->assertEquals("false", $result);
	}

	public function testBinaryOrReturnTypeTrue(): void {
		$result = $this->executeCodeSnippet(
			"or(2);",
			valueDeclarations: "or = ^x: Integer<2..4> => True :: x || false;"
		);
		$this->assertEquals("true", $result);
	}

	public function testBinaryOrReturnTypeBoolean(): void {
		$result = $this->executeCodeSnippet(
			"or(2);",
			valueDeclarations: "or = ^x: Integer<0..4> => Boolean :: x || false;"
		);
		$this->assertEquals("true", $result);
	}

	public function testBinaryOrEarlyReturn(): void {
		$result = $this->executeCodeSnippet(
			"or(2);",
			valueDeclarations: "or = ^x: Integer<0..4> => Integer<0..4> :: { => x } || true;"
		);
		$this->assertEquals("2", $result);
	}

}