<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\True;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class AsStringTest extends CodeExecutionTestHelper {

	public function testAsStringTrue(): void {
		$result = $this->executeCodeSnippet("true->asString;");
		$this->assertEquals("'true'", $result);
	}

	public function testAsStringType(): void {
		$result = $this->executeCodeSnippet("asStr(true);",
			valueDeclarations: "asStr = ^b: True => 'true' :: b->as(`String);"
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