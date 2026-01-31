<?php

namespace Walnut\Lang\Test\Almond\Engine\Implementation\Expression;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class VariableNameExpressionTest extends CodeExecutionTestHelper {

	public function testVariableName(): void {
		$result = $this->executeCodeSnippet("#;");
		$this->assertEquals("[]", $result);
	}

	public function testVariableParameterNameTuple(): void {
		$result = $this->executeCodeSnippet("recFn[1];",
			valueDeclarations: "recFn = ^[Integer] => Integer :: #0;");
		$this->assertEquals("1", $result);
	}

	public function testVariableParameterNameRecord(): void {
		$result = $this->executeCodeSnippet("recFn[a: 1];",
			valueDeclarations: "recFn = ^[a: Integer] => Integer :: #a;");
		$this->assertEquals("1", $result);
	}

	public function testVariableTargetNameTuple(): void {
		$result = $this->executeCodeSnippet("{A[1]}->recFn;",
			'A := #[Integer]; A->recFn(=> Integer) :: $0;');
		$this->assertEquals("1", $result);
	}

	public function testVariableTargetNameRecord(): void {
		$result = $this->executeCodeSnippet("{A[a: 1]}->recFn;",
			'A := #[a: Integer]; A->recFn(=> Integer) :: $a;');
		$this->assertEquals("1", $result);
	}

	public function testVariableDependencyNameTuple(): void {
		$result = $this->executeCodeSnippet("recFn();",
			'A = Integer; ==> A :: 1;', 'recFn = ^Null => Integer %% [A] :: %0;');
		$this->assertEquals("1", $result);
	}

	public function testVariableDependencyNameRecord(): void {
		$result = $this->executeCodeSnippet("recFn();",
			'A = Integer; ==> A :: 1;', 'recFn = ^Null => Integer %% [~A] :: %a;');
		$this->assertEquals("1", $result);
	}

}