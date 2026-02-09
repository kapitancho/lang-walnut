<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Boolean;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class AsIntegerTest extends CodeExecutionTestHelper {

	public function testAsIntegerFalse(): void {
		$result = $this->executeCodeSnippet("false->asInteger;");
		$this->assertEquals("0", $result);
	}

	public function testAsIntegerTrue(): void {
		$result = $this->executeCodeSnippet("true->asInteger;");
		$this->assertEquals("1", $result);
	}

	public function testAsIntegerType(): void {
		$result = $this->executeCodeSnippet("asInt(true);",
			valueDeclarations: 'asInt = ^b: Boolean => Integer[0, 1] :: b->as(`Integer);'
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