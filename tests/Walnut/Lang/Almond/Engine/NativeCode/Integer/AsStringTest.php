<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Integer;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class AsStringTest extends CodeExecutionTestHelper {

	public function testAsString(): void {
		$result = $this->executeCodeSnippet("42->asString;");
		$this->assertEquals("'42'", $result);
	}

	public function testAsStringType(): void {
		$result = $this->executeCodeSnippet("asStr(42);",
			valueDeclarations: "asStr = ^b: Integer<1..99> => String<1..2> :: b->as(`String);"
		);
		$this->assertEquals("'42'", $result);
	}

	public function testAsStringSubsetType(): void {
		$result = $this->executeCodeSnippet("asStr(42);",
			valueDeclarations: "asStr = ^b: Integer[42, -3] => String['42', '-3'] :: b->as(`String);"
		);
		$this->assertEquals("'42'", $result);
	}

	public function testAsStringInvalidParameterType(): void {
		$this->executeErrorCodeSnippet(
			"Invalid parameter type",
			"42->asString(1);"
		);
	}

}