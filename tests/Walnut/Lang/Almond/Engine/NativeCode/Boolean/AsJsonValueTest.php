<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Boolean;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class AsJsonValueTest extends CodeExecutionTestHelper {

	public function testAsJsonValueFalse(): void {
		$result = $this->executeCodeSnippet("false->asJsonValue;");
		$this->assertEquals("false", $result);
	}

	public function testAsJsonValueTrue(): void {
		$result = $this->executeCodeSnippet("true->asJsonValue;");
		$this->assertEquals("true", $result);
	}

	public function testAsJsonValueType(): void {
		$result = $this->executeCodeSnippet("asJ(true);",
			valueDeclarations: "asJ = ^b: Boolean => JsonValue :: b->as(`JsonValue);"
		);
		$this->assertEquals("true", $result);
	}

	public function testAsJsonValueInvalidParameterType(): void {
		$this->executeErrorCodeSnippet(
			"Invalid parameter type",
			"true->asJsonValue(1);"
		);
	}

}