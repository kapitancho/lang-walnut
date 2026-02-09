<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\False;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class AsStringTest extends CodeExecutionTestHelper {

	public function testAsStringFalse(): void {
		$result = $this->executeCodeSnippet("false->asString;");
		$this->assertEquals("'false'", $result);
	}

	public function testAsStringType(): void {
		$result = $this->executeCodeSnippet("asStr(false);",
			valueDeclarations: "asStr = ^b: False => 'false' :: b->as(`String);"
		);
		$this->assertEquals("'false'", $result);
	}

	public function testAsStringInvalidParameterType(): void {
		$this->executeErrorCodeSnippet(
			"Invalid parameter type",
			"false->asString(1);"
		);
	}

}