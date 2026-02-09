<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\True;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class AsRealTest extends CodeExecutionTestHelper {

	public function testAsRealTrue(): void {
		$result = $this->executeCodeSnippet("true->asReal;");
		$this->assertEquals("1", $result);
	}

	public function testAsRealType(): void {
		$result = $this->executeCodeSnippet("asInt(true);",
			valueDeclarations: 'asInt = ^b: True => 1.0 :: b->as(`Real);'
		);
		$this->assertEquals("1", $result);
	}

	public function testAsRealInvalidParameterType(): void {
		$this->executeErrorCodeSnippet(
			"Invalid parameter type",
			"true->asReal(1);"
		);
	}

}