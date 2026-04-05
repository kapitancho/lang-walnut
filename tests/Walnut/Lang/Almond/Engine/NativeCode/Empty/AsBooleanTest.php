<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Empty;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class AsBooleanTest extends CodeExecutionTestHelper {

	public function testAsBooleanEmpty(): void {
		$result = $this->executeCodeSnippet("empty->asBoolean;");
		$this->assertEquals("false", $result);
	}

	public function testAsBooleanType(): void {
		$result = $this->executeCodeSnippet("bool(empty);",
			valueDeclarations: 'bool = ^b: Empty => False :: b->as(`Boolean);'
		);
		$this->assertEquals("false", $result);
	}

	public function testAsIntegerInvalidParameterType(): void {
		$this->executeErrorCodeSnippet(
			"Invalid parameter type",
			"empty->asBoolean(1);"
		);
	}

}