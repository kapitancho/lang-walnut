<?php

namespace Walnut\Lang\NativeCode\JsonValue;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class StringifyTest extends CodeExecutionTestHelper {

	public function testStringifyNull(): void {
		$result = $this->executeCodeSnippet("null->stringify;");
		$this->assertEquals("'null'", $result);
	}

	public function testStringifyTrue(): void {
		$result = $this->executeCodeSnippet("true->stringify;");
		$this->assertEquals("'true'", $result);
	}

	public function testStringifyFalse(): void {
		$result = $this->executeCodeSnippet("false->stringify;");
		$this->assertEquals("'false'", $result);
	}

	public function testStringifyInteger(): void {
		$result = $this->executeCodeSnippet("5->stringify;");
		$this->assertEquals("'5'", $result);
	}

	public function testStringifyReal(): void {
		$result = $this->executeCodeSnippet("3.14->stringify;");
		$this->assertEquals("'3.14'", $result);
	}

	public function testStringifyString(): void {
		$result = $this->executeCodeSnippet("'hi'->stringify;");
		$this->assertEquals("'\"hi\"'", $result);
	}

	public function testStringifyTuple(): void {
		$result = $this->executeCodeSnippet("[1, 2]->stringify;");
		$this->assertEquals("'[\\n    1,\\n    2\\n]'", $result);
	}

	public function testStringifyRecord(): void {
		$result = $this->executeCodeSnippet("[a: 1, b: 2]->stringify;");
		$this->assertEquals("'{\\n    \"a\": 1,\\n    \"b\": 2\\n}'", $result);
	}

	public function testStringifySet(): void {
		$result = $this->executeCodeSnippet("[1; 2]->stringify;");
		$this->assertEquals("'[\\n    1,\\n    2\\n]'", $result);
	}

	public function testStringifySubtype(): void {
		$result = $this->executeCodeSnippet("getReal()->stringify;",
			"MySubtype <: Real; getReal = ^Any => Real :: MySubtype(3.14);");
		$this->assertEquals("'3.14'", $result);
	}

	public function testStringifyMutable(): void {
		$result = $this->executeCodeSnippet("mutable{Real, 3.14}->stringify;");
		$this->assertEquals("'3.14'", $result);
	}

}