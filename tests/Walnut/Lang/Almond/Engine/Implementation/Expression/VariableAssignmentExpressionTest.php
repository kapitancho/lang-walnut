<?php

namespace Walnut\Lang\Test\Almond\Engine\Implementation\Expression;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class VariableAssignmentExpressionTest extends CodeExecutionTestHelper {

	public function testVariableAssignment(): void {
		$result = $this->executeCodeSnippet("v = #;");
		$this->assertEquals("[]", $result);
	}

	public function testVariableAssignmentFunction(): void {
		$result = $this->executeCodeSnippet("v = ^Any => Any :: v;");
		$this->assertEquals("^Any => Any :: v", $result);
	}

	public function testVariableAssignmentFunctionWithError(): void {
		$this->executeErrorCodeSnippet("Function body return type '^Any => Integer' is not compatible with declared return type 'Integer'", "v = ^Any => Integer :: v;");
	}

}