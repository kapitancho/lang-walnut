<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Null;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class AsRealTest extends CodeExecutionTestHelper {

	public function testAsRealNull(): void {
		$result = $this->executeCodeSnippet("null->asReal;");
		$this->assertEquals("0", $result);
	}

	public function testAsRealType(): void {
		$result = $this->executeCodeSnippet("asInt(null);",
			valueDeclarations: 'asInt = ^n: Null => Real[0] :: n->as(`Real);'
		);
		$this->assertEquals("0", $result);
	}

	public function testAsRealInvalidParameterType(): void {
		$this->executeErrorCodeSnippet(
			"Invalid parameter type",
			"null->asReal(1);"
		);
	}

}