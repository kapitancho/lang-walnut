<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Boolean;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class AsStringTest extends CodeExecutionTestHelper {

	public function testAsStringFalse(): void {
		$result = $this->executeCodeSnippet("false->asString;");
		$this->assertEquals("'false'", $result);
	}

	public function testAsStringTrue(): void {
		$result = $this->executeCodeSnippet("true->asString;");
		$this->assertEquals("'true'", $result);
	}

	public function testAsStringType(): void {
		$result = $this->executeCodeSnippet("asStr(true);",
			valueDeclarations: "asStr = ^b: Boolean => String['true', 'false'] :: b->as(`String);"
		);
		$this->assertEquals("'true'", $result);
	}

	public function testAsStringInvalidParameterType(): void {
		$this->executeErrorCodeSnippet(
			"Invalid parameter type",
			"true->asString(1);"
		);
	}

}