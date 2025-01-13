<?php

namespace Walnut\Lang\Test\Implementation\Code\Expression;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class VariableNameExpressionTest extends CodeExecutionTestHelper {

	public function testVariableName(): void {
		$result = $this->executeCodeSnippet("#;");
		$this->assertEquals("[]", $result);
	}

}