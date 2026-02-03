<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Array;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class WithoutByIndexTest extends CodeExecutionTestHelper {

	public function testWithoutByIndexEmpty(): void {
		$result = $this->executeCodeSnippet("[]->withoutByIndex(3);");
		$this->assertEquals("@IndexOutOfRange![index: 3]", $result);
	}

	public function testWithoutByIndexNotFound(): void {
		$result = $this->executeCodeSnippet("[1, 2, 5, 10, 5]->withoutByIndex(13);");
		$this->assertEquals("@IndexOutOfRange![index: 13]", $result);
	}

	public function testWithoutByIndexNonEmpty(): void {
		$result = $this->executeCodeSnippet("[1, 2, 5, 10, 5]->withoutByIndex(2);");
		$this->assertEquals("[element: 5, array: [1, 2, 10, 5]]", $result);
	}

	public function testWithoutByIndexArrayInRange(): void {
		$result = $this->executeCodeSnippet(
			"w[arr: [2, 'hello', 3.14, 1.72, -4], index: 3];",
			valueDeclarations: "
				w = ^[arr: Array<String|Real, 4..8>, index: Integer<1..3>] =>
					[element: String|Real, array: Array<String|Real, 3..7>] ::
						#arr->withoutByIndex(#index);
			"
		);
		$this->assertEquals("[\n	element: 1.72,\n	array: [2, 'hello', 3.14, -4]\n]", $result);
	}

	public function testWithoutByIndexArrayNotInRangeOk(): void {
		$result = $this->executeCodeSnippet(
			"w[arr: [2, 'hello', 3.14, 1.72, -4], index: 3];",
			valueDeclarations: "
				w = ^[arr: Array<String|Real, 2..8>, index: Integer<1..3>] =>
					Result<[element: String|Real, array: Array<String|Real, 1..7>], IndexOutOfRange> ::
						#arr->withoutByIndex(#index);
			"
		);
		$this->assertEquals("[\n	element: 1.72,\n	array: [2, 'hello', 3.14, -4]\n]", $result);
	}

	public function testWithoutByIndexTupleInRange(): void {
		$result = $this->executeCodeSnippet(
			"w[arr: [2, 'hello', 3.14, 1.72, -4], index: 3];",
			valueDeclarations: "
				w = ^[arr: [Integer, String, ...Real], index: Integer<1..3>] =>
					Result<[element: String|Real, array: Array<String|Real, 1..>], IndexOutOfRange> ::
						#arr->withoutByIndex(#index);
			"
		);
		$this->assertEquals("[\n	element: 1.72,\n	array: [2, 'hello', 3.14, -4]\n]", $result);
	}

	public function testWithoutByIndexTupleWithoutRestIn(): void {
		$result = $this->executeCodeSnippet(
			"w[2, 'hello'];",
			valueDeclarations: "
				w = ^arr: [Integer, String] => [element: String, array: [Integer]] ::
					arr->withoutByIndex(1);
			"
		);
		$this->assertEquals("[element: 'hello', array: [2]]", $result);
	}

	public function testWithoutByIndexTupleWithoutRestOut(): void {
		$result = $this->executeCodeSnippet(
			"w[2, 'hello'];",
			valueDeclarations: "
				w = ^arr: [Integer, String] => Error<IndexOutOfRange> ::
					arr->withoutByIndex(3);
			"
		);
		$this->assertEquals("@IndexOutOfRange![index: 3]", $result);
	}

	public function testWithoutByIndexTupleWithRestIn(): void {
		$result = $this->executeCodeSnippet(
			"w[2, 'hello', 3.14, 1.72, -4];",
			valueDeclarations: "
				w = ^arr: [Integer, String, ...Real] => [element: String, array: [Integer, ...Real]] ::
					arr->withoutByIndex(1);
			"
		);
		$this->assertEquals("[\n	element: 'hello',\n	array: [2, 3.14, 1.72, -4]\n]", $result);
	}

	public function testWithoutByIndexTupleWithRestOut(): void {
		$result = $this->executeCodeSnippet(
			"w[2, 'hello', 3.14, 1.72, -4];",
			valueDeclarations: "
				w = ^arr: [Integer, String, ...Real] => Result<[element: Real, array: [Integer, String, ...Real]], IndexOutOfRange> ::
					arr->withoutByIndex(3);
			"
		);
		$this->assertEquals("[\n	element: 1.72,\n	array: [2, 'hello', 3.14, -4]\n]", $result);
	}

	public function testWithoutByIndexArrayNotInRangeError(): void {
		$result = $this->executeCodeSnippet(
			"w[arr: [2, 'hello'], index: 3];",
			valueDeclarations: "
				w = ^[arr: Array<String|Real, 2..8>, index: Integer<1..3>] =>
					Result<[element: String|Real, array: Array<String|Real, 1..7>], IndexOutOfRange> ::
						#arr->withoutByIndex(#index);
			"
		);
		$this->assertEquals("@IndexOutOfRange![index: 3]", $result);
	}

	public function testWithoutByIndexInvalidParameterValue(): void {
		$this->executeErrorCodeSnippet("Invalid parameter type",
			"['a', 1, 2]->withoutByIndex('b')");
	}
}