<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\False;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class AsBooleanTest extends CodeExecutionTestHelper {

	public function testAsBooleanFalse(): void {
		$result = $this->executeCodeSnippet("false->asBoolean;");
		$this->assertEquals("false", $result);
	}

	public function testAsBooleanType(): void {
		$result = $this->executeCodeSnippet("bool(false);",
			valueDeclarations: 'bool = ^b: False => False :: b->as(`Boolean);'
		);
		$this->assertEquals("false", $result);
	}

	public function testAsIntegerInvalidParameterType(): void {
		$this->executeErrorCodeSnippet(
			"Invalid parameter type",
			"false->asBoolean(1);"
		);
	}

}