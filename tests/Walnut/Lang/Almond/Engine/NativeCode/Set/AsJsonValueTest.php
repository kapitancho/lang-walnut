<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Set;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class AsJsonValueTest extends CodeExecutionTestHelper {

	public function testAsJsonValueEmpty(): void {
		$result = $this->executeCodeSnippet("[;]->asJsonValue;");
		$this->assertEquals("[]", $result);
	}

	public function testAsJsonValueNotEmpty(): void {
		$result = $this->executeCodeSnippet("[1; 'a']->asJsonValue;");
		$this->assertEquals("[1, 'a']", $result);
	}

	public function testAsJsonValueInvalid(): void {
		$result = $this->executeCodeSnippet("[1; ^ :: 1; 'a']->asJsonValue;");
		$this->assertEquals("@InvalidJsonValue![value: ^Null => Any :: 1]", $result);
	}

	public function testAsJsonValueTypeNoResult(): void {
		$result = $this->executeCodeSnippet("j[1; 'a']",
			valueDeclarations: "j = ^a: Set<Integer|String> => JsonValue :: a->asJsonValue;"
		);
		$this->assertEquals("[1, 'a']", $result);
	}

	public function testAsJsonValueTypeResultOk(): void {
		$result = $this->executeCodeSnippet("j[1; 'a']",
			valueDeclarations: "j = ^a: Set => Result<JsonValue, InvalidJsonValue> :: a->asJsonValue;"
		);
		$this->assertEquals("[1, 'a']", $result);
	}

	public function testAsJsonValueTypeResultInvalid(): void {
		$result = $this->executeCodeSnippet("j[1; ^ :: 1; 'a']",
			valueDeclarations: "j = ^a: Set => Result<JsonValue, InvalidJsonValue> :: a->asJsonValue;"
		);
		$this->assertEquals("@InvalidJsonValue![value: ^Null => Any :: 1]", $result);
	}

	public function testAsJsonValueInvalidParameterType(): void {
		$this->executeErrorCodeSnippet(
			"Invalid parameter type: Integer[5]",
			"[1; 'a']->asJsonValue(5);"
		);
	}

}