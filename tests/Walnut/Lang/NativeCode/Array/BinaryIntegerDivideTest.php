<?php

namespace Walnut\Lang\NativeCode\Array;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class BinaryIntegerDivideTest extends CodeExecutionTestHelper {

	// Basic execution tests
	public function testBinaryIntegerDivideBasic(): void {
		$result = $this->executeCodeSnippet("[1, 2, 3, 4, 5] // 2;");
		$this->assertEquals("[[1, 2], [3, 4]]", $result);
	}

	public function testBinaryIntegerDivideExactDivision(): void {
		$result = $this->executeCodeSnippet("[1, 2, 3, 4, 5, 6] // 3;");
		$this->assertEquals("[[1, 2, 3], [4, 5, 6]]", $result);
	}

	public function testBinaryIntegerDivideSizeOne(): void {
		$result = $this->executeCodeSnippet("[1, 2, 3] // 1;");
		$this->assertEquals("[[1], [2], [3]]", $result);
	}

	public function testBinaryIntegerDivideSizeLargerThanArray(): void {
		$result = $this->executeCodeSnippet("[1, 2, 3] // 10;");
		$this->assertEquals("[]", $result);
	}

	public function testBinaryIntegerDivideEmptyArray(): void {
		$result = $this->executeCodeSnippet("[] // 2;");
		$this->assertEquals("[]", $result);
	}

	public function testBinaryIntegerDivideSingleElement(): void {
		$result = $this->executeCodeSnippet("[42] // 5;");
		$this->assertEquals("[]", $result);
	}

	public function testBinaryIntegerDivideStrings(): void {
		$result = $this->executeCodeSnippet("['a', 'b', 'c', 'd', 'e'] // 2;");
		$this->assertEquals("[['a', 'b'], ['c', 'd']]", $result);
	}

	public function testBinaryIntegerDivideMixedTypes(): void {
		$result = $this->executeCodeSnippet("[1, 'two', 3.0, true, null] // 2;");
		$this->assertEquals("[[1, 'two'], [3, true]]", $result);
	}

	public function testBinaryIntegerDivideLargeArray(): void {
		$result = $this->executeCodeSnippet("[1, 2, 3, 4, 5, 6, 7, 8, 9, 10] // 3;");
		$this->assertEquals("[[1, 2, 3], [4, 5, 6], [7, 8, 9]]", $result);
	}

	// Type inference tests
	public function testBinaryIntegerDivideTypeBasic(): void {
		$result = $this->executeCodeSnippet(
			"chunk([[1, 2, 3, 4, 5], 2])",
			valueDeclarations: "chunk = ^[arr: Array<Integer, 5>, size: Integer<2>] => Array<Array<Integer, 2>, 2> :: #arr // #size;"
		);
		$this->assertEquals("[[1, 2], [3, 4]]", $result);
	}

	public function testBinaryIntegerDivideTypeMaxInfinity(): void {
		$result = $this->executeCodeSnippet(
			"chunk([[1, 2, 3, 4, 5], 2])",
			valueDeclarations: "chunk = ^[arr: Array<Integer, 5>, size: Integer<2..>] => Array<Array<Integer, 2..>, ..2> :: #arr // #size;"
		);
		$this->assertEquals("[[1, 2], [3, 4]]", $result);
	}

	public function testBinaryIntegerDivideTypeMaxInfinityEmptyArray(): void {
		$result = $this->executeCodeSnippet(
			"chunk([[1, 2, 3, 4, 5], 2])",
			valueDeclarations: "chunk = ^[arr: Array<Integer, ..5>, size: Integer<2..>] => Array<Array<Integer, 2..>, ..2> :: #arr // #size;"
		);
		$this->assertEquals("[[1, 2], [3, 4]]", $result);
	}

	public function testBinaryIntegerDivideTypeExactDivision(): void {
		$result = $this->executeCodeSnippet(
			"chunk([[1, 2, 3, 4, 5, 6], 3])",
			valueDeclarations: "chunk = ^[arr: Array<Integer, 6>, size: Integer<3>] => Array<Array<Integer, 3>, 2> :: #arr // #size;"
		);
		$this->assertEquals("[[1, 2, 3], [4, 5, 6]]", $result);
	}

	public function testBinaryIntegerDivideTypeEmptyArray(): void {
		$result = $this->executeCodeSnippet(
			"chunk([[], 2])",
			valueDeclarations: "chunk = ^[arr: Array<Integer, 0>, size: Integer<2>] => Array<Array<Integer, 0>, 0> :: #arr // #size;"
		);
		$this->assertEquals("[]", $result);
	}

	public function testBinaryIntegerDivideTypeRangeInput(): void {
		$result = $this->executeCodeSnippet(
			"chunk([[1, 2, 3, 4, 5], 2])",
			valueDeclarations: "chunk = ^[arr: Array<Integer, 3..10>, size: Integer<2..3>] => Array<Array<Integer, 2..3>, 1..5> :: #arr // #size;"
		);
		$this->assertEquals("[[1, 2], [3, 4]]", $result);
	}

	public function testBinaryIntegerDivideTypeMinimumElements(): void {
		$result = $this->executeCodeSnippet(
			"chunk([[1, 2, 3], 2])",
			valueDeclarations: "chunk = ^[arr: Array<Integer, 3>, size: Integer<2>] => Array<Array<Integer, 2>, 1> :: #arr // #size;"
		);
		$this->assertEquals("[[1, 2]]", $result);
	}

	public function testBinaryIntegerDivideTypeLargeSizeRange(): void {
		$result = $this->executeCodeSnippet(
			"chunk([[1, 2, 3, 4], 5])",
			valueDeclarations: "chunk = ^[arr: Array<Integer, 4>, size: Integer<1..10>] => Array<Array<Integer, 1..10>, ..4> :: #arr // #size;"
		);
		$this->assertEquals("[]", $result);
	}

	public function testBinaryIntegerDivideTypePreservesItemType(): void {
		$result = $this->executeCodeSnippet(
			"chunk([['a', 'b', 'c'], 2])",
			valueDeclarations: "chunk = ^[arr: Array<String, 3>, size: Integer<2>] => Array<Array<String, 2>, 1> :: #arr // #size;"
		);
		$this->assertEquals("[['a', 'b']]", $result);
	}

	public function testBinaryIntegerDivideTypeWithSubtype(): void {
		$result = $this->executeCodeSnippet(
			"chunk([[1, 2, 3, 4, 5], 2])",
			valueDeclarations: "chunk = ^[arr: Array<Integer<1..10>, 5>, size: Integer<2>] => Array<Array<Integer<1..10>, 2>, 2> :: #arr // #size;"
		);
		$this->assertEquals("[[1, 2], [3, 4]]", $result);
	}

	// Edge cases
	public function testBinaryIntegerDivideSizeEqualsLength(): void {
		$result = $this->executeCodeSnippet("[1, 2, 3, 4, 5] // 5;");
		$this->assertEquals("[[1, 2, 3, 4, 5]]", $result);
	}

	public function testBinaryIntegerDivideTwoElements(): void {
		$result = $this->executeCodeSnippet("[1, 2] // 1;");
		$this->assertEquals("[[1], [2]]", $result);
	}

	public function testBinaryIntegerDivideNestedArrays(): void {
		$result = $this->executeCodeSnippet("[[1, 2], [3, 4], [5, 6]] // 2;");
		$this->assertEquals("[[[1, 2], [3, 4]]]", $result);
	}

	public function testBinaryIntegerDivideInvalidParameterTypeNegative(): void {
		$this->executeErrorCodeSnippet(
			"Invalid parameter type",
			"[1, 2, 3] // -3;"
		);
	}

	public function testBinaryIntegerDivideInvalidParameterTypeZero(): void {
		$this->executeErrorCodeSnippet(
			"Invalid parameter type",
			"[1, 2, 3] // 0;"
		);
	}

	public function testBinaryIntegerDivideInvalidParameterType(): void {
		$this->executeErrorCodeSnippet(
			"Invalid parameter type",
			"[1, 2, 3] // 'invalid';"
		);
	}

}
