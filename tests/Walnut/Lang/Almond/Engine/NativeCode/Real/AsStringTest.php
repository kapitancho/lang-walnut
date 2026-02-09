<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Real;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class AsStringTest extends CodeExecutionTestHelper {

	public function testAsString(): void {
		$result = $this->executeCodeSnippet("3.14->asString;");
		$this->assertEquals("'3.14'", $result);
	}

	public function testAsStringInteger(): void {
		$result = $this->executeCodeSnippet("5.0->asString;");
		$this->assertEquals("'5.0'", $result);
	}

	public function testAsStringType(): void {
		$result = $this->executeCodeSnippet("asStr(3.14);",
			valueDeclarations: "asStr = ^b: Real<1..99> => String<1..> :: b->as(`String);"
		);
		$this->assertEquals("'3.14'", $result);
	}

	public function testAsStringSubsetType(): void {
		$result = $this->executeCodeSnippet("asStr(42);",
			valueDeclarations: "asStr = ^b: Real[42, -3.14] => String['42', '-3.14'] :: b->as(`String);"
		);
		$this->assertEquals("'42'", $result);
	}

	public function testAsStringInvalidParameterType(): void {
		$this->executeErrorCodeSnippet(
			"Invalid parameter type",
			"3.14->asString(1);"
		);
	}

}