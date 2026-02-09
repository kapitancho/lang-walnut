<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Boolean;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class AsRealTest extends CodeExecutionTestHelper {

	public function testAsRealFalse(): void {
		$result = $this->executeCodeSnippet("false->asReal;");
		$this->assertEquals("0", $result);
	}

	public function testAsRealTrue(): void {
		$result = $this->executeCodeSnippet("true->asReal;");
		$this->assertEquals("1", $result);
	}

	public function testAsRealType(): void {
		$result = $this->executeCodeSnippet("asInt(true);",
			valueDeclarations: 'asInt = ^b: Boolean => Real[0, 1] :: b->as(`Real);'
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