<?php

namespace Walnut\Lang\NativeCode\Array;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class BinaryDivideTest extends CodeExecutionTestHelper {

	// Basic execution tests
	public function testBinaryDivideBasic(): void {
		$result = $this->executeCodeSnippet("[1, 2, 3, 4, 5] / 2;");
		$this->assertEquals("[[1, 2], [3, 4], [5]]", $result);
	}

	public function testBinaryDivideExactDivision(): void {
		$result = $this->executeCodeSnippet("[1, 2, 3, 4, 5, 6] / 3;");
		$this->assertEquals("[[1, 2, 3], [4, 5, 6]]", $result);
	}

	public function testBinaryDivideSizeOne(): void {
		$result = $this->executeCodeSnippet("[1, 2, 3] / 1;");
		$this->assertEquals("[[1], [2], [3]]", $result);
	}

	public function testBinaryDivideSizeLargerThanArray(): void {
		$result = $this->executeCodeSnippet("[1, 2, 3] / 10;");
		$this->assertEquals("[[1, 2, 3]]", $result);
	}

	public function testBinaryDivideEmptyArray(): void {
		$result = $this->executeCodeSnippet("[] / 2;");
		$this->assertEquals("[]", $result);
	}

	public function testBinaryDivideSingleElement(): void {
		$result = $this->executeCodeSnippet("[42] / 5;");
		$this->assertEquals("[[42]]", $result);
	}

	public function testBinaryDivideStrings(): void {
		$result = $this->executeCodeSnippet("['a', 'b', 'c', 'd', 'e'] / 2;");
		$this->assertEquals("[['a', 'b'], ['c', 'd'], ['e']]", $result);
	}

	public function testBinaryDivideMixedTypes(): void {
		$result = $this->executeCodeSnippet("[1, 'two', 3.0, true, null] / 2;");
		$this->assertEquals("[[1, 'two'], [3, true], [null]]", $result);
	}

	public function testBinaryDivideLargeArray(): void {
		$result = $this->executeCodeSnippet("[1, 2, 3, 4, 5, 6, 7, 8, 9, 10] / 3;");
		$this->assertEquals("[[1, 2, 3], [4, 5, 6], [7, 8, 9], [10]]", $result);
	}

	// Type inference tests
	public function testBinaryDivideTypeBasic(): void {
		$result = $this->executeCodeSnippet(
			"chunk([[1, 2, 3, 4, 5], 2])",
			valueDeclarations: "chunk = ^[arr: Array<Integer, 5>, size: Integer<2>] => Array<Array<Integer, 1..2>, 3> :: #arr / #size;"
		);
		$this->assertEquals("[[1, 2], [3, 4], [5]]", $result);
	}

	public function testBinaryDivideTypeMaxInfinity(): void {
		$result = $this->executeCodeSnippet(
			"chunk([[1, 2, 3, 4, 5], 2])",
			valueDeclarations: "chunk = ^[arr: Array<Integer, 5>, size: Integer<2..>] => Array<Array<Integer, 1..2>, 1..3> :: #arr / #size;"
		);
		$this->assertEquals("[[1, 2], [3, 4], [5]]", $result);
	}

	public function testBinaryDivideTypeMaxInfinityEmptyArray(): void {
		$result = $this->executeCodeSnippet(
			"chunk([[1, 2, 3, 4, 5], 2])",
			valueDeclarations: "chunk = ^[arr: Array<Integer, ..5>, size: Integer<2..>] => Array<Array<Integer, ..2>, ..3> :: #arr / #size;"
		);
		$this->assertEquals("[[1, 2], [3, 4], [5]]", $result);
	}

	public function testBinaryDivideTypeExactDivision(): void {
		$result = $this->executeCodeSnippet(
			"chunk([[1, 2, 3, 4, 5, 6], 3])",
			valueDeclarations: "chunk = ^[arr: Array<Integer, 6>, size: Integer<3>] => Array<Array<Integer, 1..3>, 2> :: #arr / #size;"
		);
		$this->assertEquals("[[1, 2, 3], [4, 5, 6]]", $result);
	}

	public function testBinaryDivideTypeEmptyArray(): void {
		$result = $this->executeCodeSnippet(
			"chunk([[], 2])",
			valueDeclarations: "chunk = ^[arr: Array<Integer, 0>, size: Integer<2>] => Array<Array<Integer, 0>, 0> :: #arr / #size;"
		);
		$this->assertEquals("[]", $result);
	}

	public function testBinaryDivideTypeRangeInput(): void {
		$result = $this->executeCodeSnippet(
			"chunk([[1, 2, 3, 4, 5], 2])",
			valueDeclarations: "chunk = ^[arr: Array<Integer, 3..10>, size: Integer<2..3>] => Array<Array<Integer, 1..3>, 1..5> :: #arr / #size;"
		);
		$this->assertEquals("[[1, 2], [3, 4], [5]]", $result);
	}

	public function testBinaryDivideTypeMinimumElements(): void {
		$result = $this->executeCodeSnippet(
			"chunk([[1, 2, 3], 2])",
			valueDeclarations: "chunk = ^[arr: Array<Integer, 3>, size: Integer<2>] => Array<Array<Integer, 1..2>, 2> :: #arr / #size;"
		);
		$this->assertEquals("[[1, 2], [3]]", $result);
	}

	public function testBinaryDivideTypeLargeSizeRange(): void {
		$result = $this->executeCodeSnippet(
			"chunk([[1, 2, 3, 4], 5])",
			valueDeclarations: "chunk = ^[arr: Array<Integer, 4>, size: Integer<1..10>] => Array<Array<Integer, 1..10>, 1..4> :: #arr / #size;"
		);
		$this->assertEquals("[[1, 2, 3, 4]]", $result);
	}

	public function testBinaryDivideTypePreservesItemType(): void {
		$result = $this->executeCodeSnippet(
			"chunk([['a', 'b', 'c'], 2])",
			valueDeclarations: "chunk = ^[arr: Array<String, 3>, size: Integer<2>] => Array<Array<String, 1..2>, 2> :: #arr / #size;"
		);
		$this->assertEquals("[['a', 'b'], ['c']]", $result);
	}

	public function testBinaryDivideTypeWithSubtype(): void {
		$result = $this->executeCodeSnippet(
			"chunk([[1, 2, 3, 4, 5], 2])",
			valueDeclarations: "chunk = ^[arr: Array<Integer<1..10>, 5>, size: Integer<2>] => Array<Array<Integer<1..10>, 1..2>, 3> :: #arr / #size;"
		);
		$this->assertEquals("[[1, 2], [3, 4], [5]]", $result);
	}

	// Edge cases
	public function testBinaryDivideSizeEqualsLength(): void {
		$result = $this->executeCodeSnippet("[1, 2, 3, 4, 5] / 5;");
		$this->assertEquals("[[1, 2, 3, 4, 5]]", $result);
	}

	public function testBinaryDivideTwoElements(): void {
		$result = $this->executeCodeSnippet("[1, 2] / 1;");
		$this->assertEquals("[[1], [2]]", $result);
	}

	public function testBinaryDivideNestedArrays(): void {
		$result = $this->executeCodeSnippet("[[1, 2], [3, 4], [5, 6]] / 2;");
		$this->assertEquals("[[[1, 2], [3, 4]], [[5, 6]]]", $result);
	}

	public function testBinaryDivideInvalidParameterTypeNegative(): void {
		$this->executeErrorCodeSnippet(
			"Invalid parameter type",
			"[1, 2, 3] / -3;"
		);
	}

	public function testBinaryDivideInvalidParameterTypeZero(): void {
		$this->executeErrorCodeSnippet(
			"Invalid parameter type",
			"[1, 2, 3] / 0;"
		);
	}

	public function testBinaryDivideInvalidParameterType(): void {
		$this->executeErrorCodeSnippet(
			"Invalid parameter type",
			"[1, 2, 3] / 'invalid';"
		);
	}

}
