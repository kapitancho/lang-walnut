<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Null;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class AsBooleanTest extends CodeExecutionTestHelper {

	public function testAsBooleanNull(): void {
		$result = $this->executeCodeSnippet("null->asBoolean;");
		$this->assertEquals("false", $result);
	}

	public function testAsBooleanType(): void {
		$result = $this->executeCodeSnippet("bool(null);",
			valueDeclarations: 'bool = ^b: Null => False :: b->as(`Boolean);'
		);
		$this->assertEquals("false", $result);
	}

	public function testAsIntegerInvalidParameterType(): void {
		$this->executeErrorCodeSnippet(
			"Invalid parameter type",
			"null->asBoolean(1);"
		);
	}

}