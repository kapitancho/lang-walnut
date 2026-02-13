<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Any;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class JsonStringifyTest extends CodeExecutionTestHelper {

	public function testJsonStringifyNonJson(): void {
		$result = $this->executeCodeSnippet("MyAtom->jsonStringify;", "MyAtom := ();");
		$this->assertEquals("@InvalidJsonValue![value: MyAtom]", $result);
	}

	public function testJsonStringifyWithCast(): void {
		$result = $this->executeCodeSnippet(
			"MyAtom->jsonStringify;",
			"MyAtom := (); MyAtom ==> JsonValue :: 'my atom';"
		);
		$this->assertEquals("'\"my atom\"'", $result);
	}

	public function testJsonStringifyNull(): void {
		$result = $this->executeCodeSnippet("null->jsonStringify;");
		$this->assertEquals("'null'", $result);
	}

	public function testJsonStringifyTrue(): void {
		$result = $this->executeCodeSnippet("true->jsonStringify;");
		$this->assertEquals("'true'", $result);
	}

	public function testJsonStringifyFalse(): void {
		$result = $this->executeCodeSnippet("false->jsonStringify;");
		$this->assertEquals("'false'", $result);
	}

	public function testJsonStringifyInteger(): void {
		$result = $this->executeCodeSnippet("5->jsonStringify;");
		$this->assertEquals("'5'", $result);
	}

	public function testJsonStringifyReal(): void {
		$result = $this->executeCodeSnippet("3.14->jsonStringify;");
		$this->assertEquals("'3.14'", $result);
	}

	public function testJsonStringifyString(): void {
		$result = $this->executeCodeSnippet("'hi'->jsonStringify;");
		$this->assertEquals("'\"hi\"'", $result);
	}

	public function testJsonStringifyTuple(): void {
		$result = $this->executeCodeSnippet("[1, 2]->jsonStringify;");
		$this->assertEquals("'[\\n    1,\\n    2\\n]'", $result);
	}

	public function testJsonStringifyRecord(): void {
		$result = $this->executeCodeSnippet("[a: 1, b: 2]->jsonStringify;");
		$this->assertEquals("'{\\n    \"a\": 1,\\n    \"b\": 2\\n}'", $result);
	}

	public function testJsonStringifySet(): void {
		$result = $this->executeCodeSnippet("[1; 2]->jsonStringify;");
		$this->assertEquals("'[\\n    1,\\n    2\\n]'", $result);
	}

	public function testJsonStringifyMutable(): void {
		$result = $this->executeCodeSnippet("mutable{Real, 3.14}->jsonStringify;");
		$this->assertEquals("'3.14'", $result);
	}

	public function testJsonStringifyShape(): void {
		$result = $this->executeCodeSnippet("getReal()->shape(`Real)->jsonStringify;",
			valueDeclarations: "getReal = ^ => Shape<Real> :: 3.14;");
		$this->assertEquals("'3.14'", $result);
	}

}