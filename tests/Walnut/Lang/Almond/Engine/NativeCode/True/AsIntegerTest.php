<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\True;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class AsIntegerTest extends CodeExecutionTestHelper {

	public function testAsIntegerTrue(): void {
		$result = $this->executeCodeSnippet("true->asInteger;");
		$this->assertEquals("1", $result);
	}

	public function testAsIntegerType(): void {
		$result = $this->executeCodeSnippet("asInt(true);",
			valueDeclarations: 'asInt = ^b: True => 1 :: b->as(`Integer);'
		);
		$this->assertEquals("1", $result);
	}

	public function testAsIntegerInvalidParameterType(): void {
		$this->executeErrorCodeSnippet(
			"Invalid parameter type",
			"true->asInteger(1);"
		);
	}

}