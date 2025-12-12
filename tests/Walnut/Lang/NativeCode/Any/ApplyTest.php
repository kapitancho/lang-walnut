<?php

namespace Walnut\Lang\NativeCode\Any;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class ApplyTest extends CodeExecutionTestHelper {

	public function testOk(): void {
		$result = $this->executeCodeSnippet(
			"4->apply(fn);",
			valueDeclarations: "fn = ^p: Integer => Real :: p + 0.14;"
		);
		$this->assertEquals("4.14", $result);
	}

	public function testInvalidParameterType(): void {
		$this->executeErrorCodeSnippet(
			"Invalid parameter type",
			"4->apply(true);"
		);
	}

	public function testInvalidTargetType(): void {
		$this->executeErrorCodeSnippet(
			"Invalid target type",
			"4->apply(fn);",
			valueDeclarations: "fn = ^p: String => Integer :: p->length;"
		);
	}

}