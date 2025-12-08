<?php

namespace Walnut\Lang\NativeCode\Array;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class LastTest extends CodeExecutionTestHelper {

	public function testLastEmpty(): void {
		$result = $this->executeCodeSnippet("[]->last;");
		$this->assertEquals("@ItemNotFound", $result);
	}

	public function testLastNonEmpty(): void {
		$result = $this->executeCodeSnippet("[1, 2, 3]->last;");
		$this->assertEquals("3", $result);
	}

	public function testLastSingleElement(): void {
		$result = $this->executeCodeSnippet("['hello']->last;");
		$this->assertEquals("'hello'", $result);
	}

	public function testLastReturnTypeNonEmpty(): void {
		$result = $this->executeCodeSnippet(
			"getLast['a', 'b', 'c'];",
			valueDeclarations: "getLast = ^arr: Array<String, 1..> => String :: arr->last;"
		);
		$this->assertEquals("'c'", $result);
	}

	public function testLastReturnTypeMaybeEmpty(): void {
		$result = $this->executeCodeSnippet(
			"getLast['a', 'b', 'c'];",
			valueDeclarations: "getLast = ^arr: Array<String> => Result<String, ItemNotFound> :: arr->last;"
		);
		$this->assertEquals("'c'", $result);
	}

	public function testLastReturnTypeMaybeEmptyError(): void {
		$result = $this->executeCodeSnippet(
			"getLast[];",
			valueDeclarations: "getLast = ^arr: Array<String> => Result<String, ItemNotFound> :: arr->last;"
		);
		$this->assertEquals("@ItemNotFound", $result);
	}

	public function testLastWithMinLengthTwo(): void {
		$result = $this->executeCodeSnippet(
			"getLast[1, 2, 3];",
			valueDeclarations: "getLast = ^arr: Array<Integer, 2..> => Integer :: arr->last;"
		);
		$this->assertEquals("3", $result);
	}


	public function testLastTuple(): void {
		$result = $this->executeCodeSnippet(
			"getTupleLast[1, 'hello', true];",
			valueDeclarations: "getTupleLast = ^t: [Integer, String, Boolean] => Boolean :: t->last;"
		);
		$this->assertEquals("true", $result);
	}

	public function testLastTupleRestWithout(): void {
		$result = $this->executeCodeSnippet(
			"getTupleLast[1, 'hello', true];",
			valueDeclarations: "getTupleLast = ^t: [Integer, String, Boolean, ...Real] => Boolean|Real :: t->last;"
		);
		$this->assertEquals("true", $result);
	}

	public function testLastTupleRestWith(): void {
		$result = $this->executeCodeSnippet(
			"getTupleLast[1, 'hello', true, 2.79];",
			valueDeclarations: "getTupleLast = ^t: [Integer, String, Boolean, ...Real] => Boolean|Real :: t->last;"
		);
		$this->assertEquals("2.79", $result);
	}

	public function testLastEmptyTupleRest(): void {
		$result = $this->executeCodeSnippet(
			"getTupleLast[3.14, 42, -59.3];",
			valueDeclarations: "getTupleLast = ^t: [...Real] => Result<Real, ItemNotFound> :: t->last;"
		);
		$this->assertEquals("-59.3", $result);
	}

	public function testLastEmptyTupleRestEmpty(): void {
		$result = $this->executeCodeSnippet(
			"getTupleLast[];",
			valueDeclarations: "getTupleLast = ^t: [...Real] => Result<Real, ItemNotFound> :: t->last;"
		);
		$this->assertEquals("@ItemNotFound", $result);
	}

}
