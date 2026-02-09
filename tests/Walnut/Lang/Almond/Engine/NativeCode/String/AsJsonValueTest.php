<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\String;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class AsJsonValueTest extends CodeExecutionTestHelper {

	public function testAsJsonValue(): void {
		$result = $this->executeCodeSnippet("'hello'->asJsonValue;");
		$this->assertEquals("'hello'", $result);
	}

	public function testAsJsonValueType(): void {
		$result = $this->executeCodeSnippet("asStr('hello');",
			valueDeclarations: "asStr = ^b: String<3..10> => JsonValue :: b->as(`JsonValue);"
		);
		$this->assertEquals("'hello'", $result);
	}

	public function testAsJsonValueSubsetType(): void {
		$result = $this->executeCodeSnippet("asStr('hello');",
			valueDeclarations: "asStr = ^b: String['hello', 'world'] => JsonValue :: b->as(`JsonValue);"
		);
		$this->assertEquals("'hello'", $result);
	}

	public function testAsJsonValueInvalidParameterType(): void {
		$this->executeErrorCodeSnippet(
			"Invalid parameter type",
			"'hello'->asJsonValue(1);"
		);
	}

}