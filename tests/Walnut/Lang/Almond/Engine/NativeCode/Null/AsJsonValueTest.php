<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Null;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class AsJsonValueTest extends CodeExecutionTestHelper {

	public function testAsJsonValueNull(): void {
		$result = $this->executeCodeSnippet("null->asJsonValue;");
		$this->assertEquals("null", $result);
	}

	public function testAsJsonValueType(): void {
		$result = $this->executeCodeSnippet("asJ(null);",
			valueDeclarations: "asJ = ^b: Null => JsonValue :: b->as(`JsonValue);"
		);
		$this->assertEquals("null", $result);
	}

	public function testAsJsonValueInvalidParameterType(): void {
		$this->executeErrorCodeSnippet(
			"Invalid parameter type",
			"null->asJsonValue(1);"
		);
	}

}