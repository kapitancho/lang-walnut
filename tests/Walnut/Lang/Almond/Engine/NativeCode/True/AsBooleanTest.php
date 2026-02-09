<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\True;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class AsBooleanTest extends CodeExecutionTestHelper {

	public function testAsBooleanTrue(): void {
		$result = $this->executeCodeSnippet("true->asBoolean;");
		$this->assertEquals("true", $result);
	}

	public function testAsBooleanType(): void {
		$result = $this->executeCodeSnippet("bool(true);",
			valueDeclarations: 'bool = ^b: True => True :: b->as(`Boolean);'
		);
		$this->assertEquals("true", $result);
	}

	public function testAsIntegerInvalidParameterType(): void {
		$this->executeErrorCodeSnippet(
			"Invalid parameter type",
			"true->asBoolean(1);"
		);
	}

}