<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\False;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class AsIntegerTest extends CodeExecutionTestHelper {

	public function testAsIntegerFalse(): void {
		$result = $this->executeCodeSnippet("false->asInteger;");
		$this->assertEquals("0", $result);
	}

	public function testAsIntegerType(): void {
		$result = $this->executeCodeSnippet("asInt(false);",
			valueDeclarations: 'asInt = ^b: False => 0 :: b->as(`Integer);'
		);
		$this->assertEquals("0", $result);
	}

	public function testAsIntegerInvalidParameterType(): void {
		$this->executeErrorCodeSnippet(
			"Invalid parameter type",
			"true->asInteger(1);"
		);
	}

}