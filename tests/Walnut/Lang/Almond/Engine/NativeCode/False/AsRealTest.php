<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\False;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class AsRealTest extends CodeExecutionTestHelper {

	public function testAsRealFalse(): void {
		$result = $this->executeCodeSnippet("false->asReal;");
		$this->assertEquals("0", $result);
	}

	public function testAsRealType(): void {
		$result = $this->executeCodeSnippet("asInt(false);",
			valueDeclarations: 'asInt = ^b: False => 0.0 :: b->as(`Real);'
		);
		$this->assertEquals("0", $result);
	}

	public function testAsRealInvalidParameterType(): void {
		$this->executeErrorCodeSnippet(
			"Invalid parameter type",
			"false->asReal(1);"
		);
	}

}