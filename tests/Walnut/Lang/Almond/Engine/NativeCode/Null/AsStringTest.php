<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Null;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class AsStringTest extends CodeExecutionTestHelper {

	public function testAsStringNull(): void {
		$result = $this->executeCodeSnippet("null->asString;");
		$this->assertEquals("'null'", $result);
	}

	public function testAsStringType(): void {
		$result = $this->executeCodeSnippet("asStr(null);",
			valueDeclarations: "asStr = ^b: Null => 'null' :: b->as(`String);"
		);
		$this->assertEquals("'null'", $result);
	}

	public function testAsStringInvalidParameterType(): void {
		$this->executeErrorCodeSnippet(
			"Invalid parameter type",
			"null->asString(1);"
		);
	}

}