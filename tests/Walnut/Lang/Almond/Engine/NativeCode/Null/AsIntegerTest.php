<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Null;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class AsIntegerTest extends CodeExecutionTestHelper {

	public function testAsIntegerNull(): void {
		$result = $this->executeCodeSnippet("null->asInteger;");
		$this->assertEquals("0", $result);
	}

	public function testAsIntegerType(): void {
		$result = $this->executeCodeSnippet("asInt(null);",
			valueDeclarations: 'asInt = ^n: Null => Integer[0] :: n->as(`Integer);'
		);
		$this->assertEquals("0", $result);
	}

	public function testAsIntegerInvalidParameterType(): void {
		$this->executeErrorCodeSnippet(
			"Invalid parameter type",
			"null->asInteger(1);"
		);
	}

}