<?php

namespace Walnut\Lang\Test\Implementation\Code\Expression;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class ConstantExpressionTest extends CodeExecutionTestHelper {

	public function testConstant(): void {
		$result = $this->executeCodeSnippet("5;");
		$this->assertEquals("5", $result);
	}

	public function testConstantFunction(): void {
		$result = $this->executeCodeSnippet("x = 1; fn = ^Null => Integer :: x;");
		$this->assertEquals("^Null => Integer :: x", $result);
	}

	public function testWithDependencyError(): void {
		$this->executeErrorCodeSnippet(
			"the dependency T cannot be resolved",
			"fn = ^Null => Integer %% T :: 1;",
			"T :=  String;"
		);
	}

}