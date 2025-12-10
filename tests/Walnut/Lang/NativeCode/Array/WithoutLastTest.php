<?php

namespace Walnut\Lang\Test\NativeCode\Array;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class WithoutLastTest extends CodeExecutionTestHelper {

	public function testWithoutLastEmpty(): void {
		$result = $this->executeCodeSnippet("[]->withoutLast;");
		$this->assertEquals("@ItemNotFound", $result);
	}

	public function testWithoutLastNonEmpty(): void {
		$result = $this->executeCodeSnippet("[1, 2]->withoutLast;");
		$this->assertEquals("[element: 2, array: [1]]", $result);
	}
	
	public function testWithoutLastNonEmptyArray(): void {
		$result = $this->executeCodeSnippet(
			"t[1, 3.14, 'hello'];",
			valueDeclarations: "
				t = ^arr: Array<Real|String, 2..5> => [element: Real|String, array: Array<Real|String, 1..4>] :: 
					arr->withoutLast;
			"
		);
		$this->assertEquals("[element: 'hello', array: [1, 3.14]]", $result);
	}

	public function testWithoutLastArrayNotEmpty(): void {
		$result = $this->executeCodeSnippet(
			"t[1, 3.14, 'hello'];",
			valueDeclarations: "
				t = ^arr: Array<Real|String, ..5> => Result<[element: Real|String, array: Array<Real|String, ..4>], ItemNotFound> :: 
					arr->withoutLast;
			"
		);
		$this->assertEquals("[element: 'hello', array: [1, 3.14]]", $result);
	}

	public function testWithoutLastArrayEmpty(): void {
		$result = $this->executeCodeSnippet(
			"t[];",
			valueDeclarations: "
				t = ^arr: Array<Real|String, ..5> => Result<[element: Real|String, array: Array<Real|String, ..4>], ItemNotFound> :: 
					arr->withoutLast;
			"
		);
		$this->assertEquals("@ItemNotFound", $result);
	}

	public function testWithoutLastTupleEmpty(): void {
		$result = $this->executeCodeSnippet(
			"t[];",
			valueDeclarations: "
				t = ^arr: [] => Error<ItemNotFound> :: arr->withoutLast;
			"
		);
		$this->assertEquals("@ItemNotFound", $result);
	}

	public function testWithoutLastTupleEmptyRest(): void {
		$result = $this->executeCodeSnippet(
			"t[];",
			valueDeclarations: "
				t = ^arr: [... String] => Result<[element: String, array: [... String]], ItemNotFound> :: arr->withoutLast;
			"
		);
		$this->assertEquals("@ItemNotFound", $result);
	}

	public function testWithoutLastTupleWithRest(): void {
		$result = $this->executeCodeSnippet(
			"t[1, 3.14, 'hello'];",
			valueDeclarations: "
				t = ^arr: [Integer, Real, ...String] => [element: Real|String, array: [Integer, Real|String, ...String]] :: 
					arr->withoutLast;
			"
		);
		$this->assertEquals("[element: 'hello', array: [1, 3.14]]", $result);
	}

	public function testWithoutLastTupleNoRest(): void {
		$result = $this->executeCodeSnippet(
			"t[1, 3.14, 'hello'];",
			valueDeclarations: "
				t = ^arr: [Integer, Real, String] => [element: String, array: [Integer, Real]] :: 
					arr->withoutLast;
			"
		);
		$this->assertEquals("[element: 'hello', array: [1, 3.14]]", $result);
	}

	public function testWithoutLastInvalidParameterType(): void {
		$this->executeErrorCodeSnippet(
			"Invalid parameter type: Integer[42]",
			"[1, 2]->withoutLast(42);"
		);
	}
}