<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Any;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class IfEmptyTest extends CodeExecutionTestHelper {

	public function testIfEmptyWithAny(): void {
		$result = $this->executeCodeSnippet(
			"404->ifEmpty(^ => Integer :: 100);",
		);
		$this->assertEquals("404", $result);
	}

	public function testIfEmptyWithErrorNoTransform(): void {
		$result = $this->executeCodeSnippet(
			"makeEmpty(404)->ifEmpty(^ => Integer :: 100);",
			valueDeclarations: "
				makeEmpty = ^code: Integer => Optional<String> :: 'hello';
			"
		);
		$this->assertEquals("'hello'", $result);
	}

	public function testIfEmptyWithErrorTransform(): void {
		$result = $this->executeCodeSnippet(
			"makeEmpty(404)->ifEmpty(^ => Integer :: 100);",
			valueDeclarations: "
				makeEmpty = ^code: Integer => Optional<String> :: empty;
			"
		);
		$this->assertEquals("100", $result);
	}

	public function testIfEmptyInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type',
			"makeEmpty('x')->ifEmpty(123);",
			valueDeclarations: "
				makeEmpty = ^s: String => Optional<Integer> :: 1;
			"
		);
	}

	public function testIfEmptyInvalidCallbackParameterTypeForResult(): void {
		$this->executeErrorCodeSnippet("The parameter type Integer of the callback function is not a subtype of Null",
			"makeEmpty('x')->ifEmpty(^e: Integer => Integer :: e);",
			valueDeclarations: "
				makeEmpty = ^s: String => Optional<Integer> :: empty;
			"
		);
	}

	public function testIfEmptyWithReturnTypeInteger(): void {
		$result = $this->executeCodeSnippet(
			"ifEmpty('hello');",
			valueDeclarations: "
				ifEmpty = ^p: String => String :: p->ifEmpty(^e: Any => Any :: e);
			"
		);
		$this->assertEquals("'hello'", $result);
	}

	public function testIfEmptyWithReturnTypeAny(): void {
		$result = $this->executeCodeSnippet(
			"ifEmpty('hello');",
			valueDeclarations: "
				ifEmpty = ^p: Any => Any :: p->ifEmpty(^e: Any => Any :: e);
			"
		);
		$this->assertEquals("'hello'", $result);
	}

	public function testIfEmptyWithReturnTypeResult(): void {
		$result = $this->executeCodeSnippet(
			"ifEmpty('hello');",
			valueDeclarations: "
				ifEmpty = ^p: Optional<String> => String|Real :: p->ifEmpty(^ => Real :: 3.14);
			"
		);
		$this->assertEquals("'hello'", $result);
	}

	public function testIfEmptyWithReturnTypeResultError(): void {
		$result = $this->executeCodeSnippet(
			"ifEmpty(empty);",
			valueDeclarations: "
				ifEmpty = ^p: Optional<String> => String|Real :: p->ifEmpty(^ => Real :: 3.14);
			"
		);
		$this->assertEquals("3.14", $result);
	}

	public function testIfEmptyWithReturnTypeOptionalNothing(): void {
		$result = $this->executeCodeSnippet(
			"ifEmpty(empty);",
			valueDeclarations: "
				ifEmpty = ^p: Optional<Nothing> => Real :: p->ifEmpty(^ => Real :: 3.14);
			"
		);
		$this->assertEquals("3.14", $result);
	}

	public function testIfEmptyWithReturnTypeEmpty(): void {
		$result = $this->executeCodeSnippet(
			"ifEmpty(empty);",
			valueDeclarations: "
				ifEmpty = ^p: Empty => Real :: p->ifEmpty(^ => Real :: 3.14);
			"
		);
		$this->assertEquals("3.14", $result);
	}

}
