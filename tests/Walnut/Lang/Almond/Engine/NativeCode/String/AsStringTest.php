<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\String;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class AsStringTest extends CodeExecutionTestHelper {

	public function testAsString(): void {
		$result = $this->executeCodeSnippet("'hello'->asString;");
		$this->assertEquals("'hello'", $result);
	}

	public function testAsStringType(): void {
		$result = $this->executeCodeSnippet("asStr('hello');",
			valueDeclarations: "asStr = ^b: String<3..10> => String<3..10> :: b->as(`String);"
		);
		$this->assertEquals("'hello'", $result);
	}

	public function testAsStringSubsetType(): void {
		$result = $this->executeCodeSnippet("asStr('hello');",
			valueDeclarations: "asStr = ^b: String['hello', 'world'] => String['hello', 'world'] :: b->as(`String);"
		);
		$this->assertEquals("'hello'", $result);
	}

	public function testAsStringInvalidParameterType(): void {
		$this->executeErrorCodeSnippet(
			"Invalid parameter type",
			"'hello'->asString(1);"
		);
	}

}