<?php

namespace Walnut\Lang\Test\Implementation\Code\Expression;

use Walnut\Lang\Test\CodeExecutionTestHelper;

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
		$this->executeErrorCodeSnippet("Error in function assigned to variable", "v = ^Any => Integer :: v;");
	}

}