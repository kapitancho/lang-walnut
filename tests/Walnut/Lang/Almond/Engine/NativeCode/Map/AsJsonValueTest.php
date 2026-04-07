<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Map;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class AsJsonValueTest extends CodeExecutionTestHelper {

	public function testAsJsonValueEmpty(): void {
		$result = $this->executeCodeSnippet("[:]->asJsonValue;");
		$this->assertEquals("[:]", $result);
	}

	public function testAsJsonValueNotEmpty(): void {
		$result = $this->executeCodeSnippet("[a: 1, b: 'a']->asJsonValue;");
		$this->assertEquals("[a: 1, b: 'a']", $result);
	}

	public function testAsJsonValueInvalid(): void {
		$result = $this->executeCodeSnippet("[a: 1, b: ^ :: 1, c: 'a']->asJsonValue;");
		$this->assertEquals("@InvalidJsonValue![value: ^Null => Optional :: 1]", $result);
	}

	public function testAsJsonValueTypeNoResult(): void {
		$result = $this->executeCodeSnippet("j[a: 1, b: 'a']",
			valueDeclarations: "j = ^a: Map<Integer|String> => JsonValue :: a->asJsonValue;"
		);
		$this->assertEquals("[a: 1, b: 'a']", $result);
	}

	public function testAsJsonValueTypeResultOk(): void {
		$result = $this->executeCodeSnippet("j[a: 1, b: 'a']",
			valueDeclarations: "j = ^a: Map => Result<JsonValue, InvalidJsonValue> :: a->asJsonValue;"
		);
		$this->assertEquals("[a: 1, b: 'a']", $result);
	}

	public function testAsJsonValueTypeResultInvalid(): void {
		$result = $this->executeCodeSnippet("j[a: 1, b: ^ :: 1, c: 'a']",
			valueDeclarations: "j = ^a: Map => Result<JsonValue, InvalidJsonValue> :: a->asJsonValue;"
		);
		$this->assertEquals("@InvalidJsonValue![value: ^Null => Optional :: 1]", $result);
	}

	public function testAsJsonValueInvalidParameterType(): void {
		$this->executeErrorCodeSnippet(
			"Invalid parameter type: Integer[5]",
			"[a: 1, b: 'a']->asJsonValue(5);"
		);
	}

}