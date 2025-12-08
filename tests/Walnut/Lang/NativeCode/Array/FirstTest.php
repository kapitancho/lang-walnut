<?php

namespace Walnut\Lang\NativeCode\Array;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class FirstTest extends CodeExecutionTestHelper {

	public function testFirstEmpty(): void {
		$result = $this->executeCodeSnippet("[]->first;");
		$this->assertEquals("@ItemNotFound", $result);
	}

	public function testFirstNonEmpty(): void {
		$result = $this->executeCodeSnippet("[1, 2, 3]->first;");
		$this->assertEquals("1", $result);
	}

	public function testFirstSingleElement(): void {
		$result = $this->executeCodeSnippet("['hello']->first;");
		$this->assertEquals("'hello'", $result);
	}

	public function testFirstReturnTypeNonEmpty(): void {
		$result = $this->executeCodeSnippet(
			"getFirst['a', 'b', 'c'];",
			valueDeclarations: "getFirst = ^arr: Array<String, 1..> => String :: arr->first;"
		);
		$this->assertEquals("'a'", $result);
	}

	public function testFirstReturnTypeMaybeEmpty(): void {
		$result = $this->executeCodeSnippet(
			"getFirst['a', 'b', 'c'];",
			valueDeclarations: "getFirst = ^arr: Array<String> => Result<String, ItemNotFound> :: arr->first;"
		);
		$this->assertEquals("'a'", $result);
	}

	public function testFirstReturnTypeMaybeEmptyError(): void {
		$result = $this->executeCodeSnippet(
			"getFirst[];",
			valueDeclarations: "getFirst = ^arr: Array<String> => Result<String, ItemNotFound> :: arr->first;"
		);
		$this->assertEquals("@ItemNotFound", $result);
	}

	public function testFirstWithMinLengthTwo(): void {
		$result = $this->executeCodeSnippet(
			"getFirst[1, 2, 3];",
			valueDeclarations: "getFirst = ^arr: Array<Integer, 2..> => Integer :: arr->first;"
		);
		$this->assertEquals("1", $result);
	}

	public function testFirstTuple(): void {
		$result = $this->executeCodeSnippet(
			"getTupleFirst[1, 'hello', true];",
			valueDeclarations: "getTupleFirst = ^t: [Integer, String, Boolean] => Integer :: t->first;"
		);
		$this->assertEquals("1", $result);
	}

	public function testFirstTupleRest(): void {
		$result = $this->executeCodeSnippet(
			"getTupleFirst[1, 'hello', true];",
			valueDeclarations: "getTupleFirst = ^t: [Integer, String, Boolean, ...Real] => Integer :: t->first;"
		);
		$this->assertEquals("1", $result);
	}

	public function testFirstEmptyTupleRest(): void {
		$result = $this->executeCodeSnippet(
			"getTupleFirst[3.14, 42, -59.3];",
			valueDeclarations: "getTupleFirst = ^t: [...Real] => Result<Real, ItemNotFound> :: t->first;"
		);
		$this->assertEquals("3.14", $result);
	}

	public function testFirstEmptyTupleRestEmpty(): void {
		$result = $this->executeCodeSnippet(
			"getTupleFirst[];",
			valueDeclarations: "getTupleFirst = ^t: [...Real] => Result<Real, ItemNotFound> :: t->first;"
		);
		$this->assertEquals("@ItemNotFound", $result);
	}

	public function testInvalidParameterType(): void {
		$this->executeErrorCodeSnippet(
			"Invalid parameter type",
			"[]->first(1);"
		);
	}

}
