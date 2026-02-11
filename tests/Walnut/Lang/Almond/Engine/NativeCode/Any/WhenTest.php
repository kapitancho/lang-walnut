<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Any;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class WhenTest extends CodeExecutionTestHelper {

	public function testWhenSuccessBranchAny(): void {
		$result = $this->executeCodeSnippet(
			"doWhen(42);",
			valueDeclarations: "
				doWhen = ^a: Any :: a->when[
					success: ^i: Any => Integer :: 42,
					error: ^e: Any => Integer :: -1
				];
			"
		);
		$this->assertEquals("42", $result);
	}

	public function testWhenSuccessBranchNotAny(): void {
		$result = $this->executeCodeSnippet(
			"doWhen(42);",
			valueDeclarations: "
				doWhen = ^a: Integer :: a->when[
					success: ^i: Any => Integer :: 42,
					error: ^e: Any => Integer :: -1
				];
			"
		);
		$this->assertEquals("42", $result);
	}

	public function testWhenSuccessBranch(): void {
		$result = $this->executeCodeSnippet(
			"doWhen(42);",
			valueDeclarations: "
				doWhen = ^a: Result<Integer, String> :: a->when[
					success: ^i: Integer => Integer :: i + 1,
					error: ^e: String => Integer :: e->length
				];
			"
		);
		$this->assertEquals("43", $result);
	}

	public function testWhenErrorBranch(): void {
		$result = $this->executeCodeSnippet(
			"doWhen(@'error');",
			valueDeclarations: "
				doWhen = ^a: Result<Integer, String> :: a->when[
					success: ^i: Integer => Integer :: i + 1,
					error: ^e: String => Integer :: e->length
				];
			"
		);
		$this->assertEquals("5", $result);
	}

	public function testWhenInvalidParameterType(): void {
		$this->executeErrorCodeSnippet("Invalid parameter type: Integer[42]",
			"^a: Result<Integer, String> :: a->when(42);"
		);
	}

	public function testWhenInvalidParameterTypeSuccessKey(): void {
		$this->executeErrorCodeSnippet("Invalid parameter type: [",
			"^a: Result<Integer, String> :: a->when[success: 42, error: ^e: String :: e->length];"
		);
	}

	public function testWhenInvalidParameterTypeErrorKey(): void {
		$this->executeErrorCodeSnippet("Invalid parameter type: [",
			"^a: Result<Integer, String> :: a->when[success: ^i: Integer => Integer :: i + 1, error: 53];"
		);
	}

	public function testWhenInvalidParameterTypeMissingKey(): void {
		$this->executeErrorCodeSnippet("Invalid parameter type: [",
			"^a: Result<Integer, String> :: a->when[success: ^i: Integer => Integer :: i + 1];"
		);
	}

}
