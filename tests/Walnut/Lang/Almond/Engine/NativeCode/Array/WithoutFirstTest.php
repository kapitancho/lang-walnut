<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Array;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class WithoutFirstTest extends CodeExecutionTestHelper {

	public function testWithoutFirstEmpty(): void {
		$result = $this->executeCodeSnippet("[]->withoutFirst;");
		$this->assertEquals("@ItemNotFound", $result);
	}

	public function testWithoutFirstNonEmpty(): void {
		$result = $this->executeCodeSnippet("[1, 2]->withoutFirst;");
		$this->assertEquals("[element: 1, array: [2]]", $result);
	}

	public function testWithoutFirstNonEmptyArray(): void {
		$result = $this->executeCodeSnippet(
			"t[1, 3.14, 'hello'];",
			valueDeclarations: "
				t = ^arr: Array<Real|String, 2..5> => [element: Real|String, array: Array<Real|String, 1..4>] :: 
					arr->withoutFirst;
			"
		);
		$this->assertEquals("[element: 1, array: [3.14, 'hello']]", $result);
	}

	public function testWithoutFirstArrayNotEmpty(): void {
		$result = $this->executeCodeSnippet(
			"t[1, 3.14, 'hello'];",
			valueDeclarations: "
				t = ^arr: Array<Real|String, ..5> => Result<[element: Real|String, array: Array<Real|String, ..4>], ItemNotFound> :: 
					arr->withoutFirst;
			"
		);
		$this->assertEquals("[element: 1, array: [3.14, 'hello']]", $result);
	}

	public function testWithoutFirstArrayEmpty(): void {
		$result = $this->executeCodeSnippet(
			"t[];",
			valueDeclarations: "
				t = ^arr: Array<Real|String, ..5> => Result<[element: Real|String, array: Array<Real|String, ..4>], ItemNotFound> :: 
					arr->withoutFirst;
			"
		);
		$this->assertEquals("@ItemNotFound", $result);
	}

	public function testWithoutFirstTupleEmpty(): void {
		$result = $this->executeCodeSnippet(
			"t[];",
			valueDeclarations: "
				t = ^arr: [] => Error<ItemNotFound> :: arr->withoutFirst;
			"
		);
		$this->assertEquals("@ItemNotFound", $result);
	}

	public function testWithoutFirstTupleEmptyRest(): void {
		$result = $this->executeCodeSnippet(
			"t[];",
			valueDeclarations: "
				t = ^arr: [... String] => Result<[element: String, array: [... String]], ItemNotFound> :: arr->withoutFirst;
			"
		);
		$this->assertEquals("@ItemNotFound", $result);
	}

	public function testWithoutFirstTupleWithRest(): void {
		$result = $this->executeCodeSnippet(
			"t[1, 3.14, 'hello'];",
			valueDeclarations: "
				t = ^arr: [Integer, Real, ...String] => [element: Integer, array: [Real, ...String]] :: 
					arr->withoutFirst;
			"
		);
		$this->assertEquals("[element: 1, array: [3.14, 'hello']]", $result);
	}

	public function testWithoutFirstTupleNoRest(): void {
		$result = $this->executeCodeSnippet(
			"t[1, 3.14, 'hello'];",
			valueDeclarations: "
				t = ^arr: [Integer, Real, String] => [element: Integer, array: [Real, String]] :: 
					arr->withoutFirst;
			"
		);
		$this->assertEquals("[element: 1, array: [3.14, 'hello']]", $result);
	}

	public function testWithoutFirstInvalidParameterType(): void {
		$this->executeErrorCodeSnippet(
			"Invalid parameter type: Integer[42]",
			"[1, 2]->withoutFirst(42);"
		);
	}

}