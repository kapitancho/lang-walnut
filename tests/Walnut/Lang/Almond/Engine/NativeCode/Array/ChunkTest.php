<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Array;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class ChunkTest extends CodeExecutionTestHelper {

	// Basic execution tests
	public function testChunkBasic(): void {
		$result = $this->executeCodeSnippet("[1, 2, 3, 4, 5]->chunk(2);");
		$this->assertEquals("[[1, 2], [3, 4], [5]]", $result);
	}

	public function testChunkExactDivision(): void {
		$result = $this->executeCodeSnippet("[1, 2, 3, 4, 5, 6]->chunk(3);");
		$this->assertEquals("[[1, 2, 3], [4, 5, 6]]", $result);
	}

	public function testChunkSizeOne(): void {
		$result = $this->executeCodeSnippet("[1, 2, 3]->chunk(1);");
		$this->assertEquals("[[1], [2], [3]]", $result);
	}

	public function testChunkSizeLargerThanArray(): void {
		$result = $this->executeCodeSnippet("[1, 2, 3]->chunk(10);");
		$this->assertEquals("[[1, 2, 3]]", $result);
	}

	public function testChunkEmptyArray(): void {
		$result = $this->executeCodeSnippet("[]->chunk(2);");
		$this->assertEquals("[]", $result);
	}

	public function testChunkSingleElement(): void {
		$result = $this->executeCodeSnippet("[42]->chunk(5);");
		$this->assertEquals("[[42]]", $result);
	}

	public function testChunkStrings(): void {
		$result = $this->executeCodeSnippet("['a', 'b', 'c', 'd', 'e']->chunk(2);");
		$this->assertEquals("[['a', 'b'], ['c', 'd'], ['e']]", $result);
	}

	public function testChunkMixedTypes(): void {
		$result = $this->executeCodeSnippet("[1, 'two', 3.0, true, null]->chunk(2);");
		$this->assertEquals("[[1, 'two'], [3, true], [null]]", $result);
	}

	public function testChunkLargeArray(): void {
		$result = $this->executeCodeSnippet("[1, 2, 3, 4, 5, 6, 7, 8, 9, 10]->chunk(3);");
		$this->assertEquals("[[1, 2, 3], [4, 5, 6], [7, 8, 9], [10]]", $result);
	}

	// Type inference tests
	public function testChunkTypeBasic(): void {
		$result = $this->executeCodeSnippet(
			"chunk([[1, 2, 3, 4, 5], 2])",
			valueDeclarations: "chunk = ^[arr: Array<Integer, 5>, size: Integer<2>] => Array<Array<Integer, 1..2>, 3> :: #arr->chunk(#size);"
		);
		$this->assertEquals("[[1, 2], [3, 4], [5]]", $result);
	}

	public function testChunkTypeMaxInfinity(): void {
		$result = $this->executeCodeSnippet(
			"chunk([[1, 2, 3, 4, 5], 2])",
			valueDeclarations: "chunk = ^[arr: Array<Integer, 5>, size: Integer<2..>] => Array<Array<Integer, 1..2>, 1..3> :: #arr->chunk(#size);"
		);
		$this->assertEquals("[[1, 2], [3, 4], [5]]", $result);
	}

	public function testChunkTypeMaxInfinityEmptyArray(): void {
		$result = $this->executeCodeSnippet(
			"chunk([[1, 2, 3, 4, 5], 2])",
			valueDeclarations: "chunk = ^[arr: Array<Integer, ..5>, size: Integer<2..>] => Array<Array<Integer, ..2>, ..3> :: #arr->chunk(#size);"
		);
		$this->assertEquals("[[1, 2], [3, 4], [5]]", $result);
	}

	public function testChunkTypeExactDivision(): void {
		$result = $this->executeCodeSnippet(
			"chunk([[1, 2, 3, 4, 5, 6], 3])",
			valueDeclarations: "chunk = ^[arr: Array<Integer, 6>, size: Integer<3>] => Array<Array<Integer, 1..3>, 2> :: #arr->chunk(#size);"
		);
		$this->assertEquals("[[1, 2, 3], [4, 5, 6]]", $result);
	}

	public function testChunkTypeEmptyArray(): void {
		$result = $this->executeCodeSnippet(
			"chunk([[], 2])",
			valueDeclarations: "chunk = ^[arr: Array<Integer, 0>, size: Integer<2>] => Array<Array<Integer, 0>, 0> :: #arr->chunk(#size);"
		);
		$this->assertEquals("[]", $result);
	}

	public function testChunkTypeRangeInput(): void {
		$result = $this->executeCodeSnippet(
			"chunk([[1, 2, 3, 4, 5], 2])",
			valueDeclarations: "chunk = ^[arr: Array<Integer, 3..10>, size: Integer<2..3>] => Array<Array<Integer, 1..3>, 1..5> :: #arr->chunk(#size);"
		);
		$this->assertEquals("[[1, 2], [3, 4], [5]]", $result);
	}

	public function testChunkTypeMinimumElements(): void {
		$result = $this->executeCodeSnippet(
			"chunk([[1, 2, 3], 2])",
			valueDeclarations: "chunk = ^[arr: Array<Integer, 3>, size: Integer<2>] => Array<Array<Integer, 1..2>, 2> :: #arr->chunk(#size);"
		);
		$this->assertEquals("[[1, 2], [3]]", $result);
	}

	public function testChunkTypeLargeSizeRange(): void {
		$result = $this->executeCodeSnippet(
			"chunk([[1, 2, 3, 4], 5])",
			valueDeclarations: "chunk = ^[arr: Array<Integer, 4>, size: Integer<1..10>] => Array<Array<Integer, 1..10>, 1..4> :: #arr->chunk(#size);"
		);
		$this->assertEquals("[[1, 2, 3, 4]]", $result);
	}

	public function testChunkTypePreservesItemType(): void {
		$result = $this->executeCodeSnippet(
			"chunk([['a', 'b', 'c'], 2])",
			valueDeclarations: "chunk = ^[arr: Array<String, 3>, size: Integer<2>] => Array<Array<String, 1..2>, 2> :: #arr->chunk(#size);"
		);
		$this->assertEquals("[['a', 'b'], ['c']]", $result);
	}

	public function testChunkTypeWithSubtype(): void {
		$result = $this->executeCodeSnippet(
			"chunk([[1, 2, 3, 4, 5], 2])",
			valueDeclarations: "chunk = ^[arr: Array<Integer<1..10>, 5>, size: Integer<2>] => Array<Array<Integer<1..10>, 1..2>, 3> :: #arr->chunk(#size);"
		);
		$this->assertEquals("[[1, 2], [3, 4], [5]]", $result);
	}

	// Edge cases
	public function testChunkSizeEqualsLength(): void {
		$result = $this->executeCodeSnippet("[1, 2, 3, 4, 5]->chunk(5);");
		$this->assertEquals("[[1, 2, 3, 4, 5]]", $result);
	}

	public function testChunkTwoElements(): void {
		$result = $this->executeCodeSnippet("[1, 2]->chunk(1);");
		$this->assertEquals("[[1], [2]]", $result);
	}

	public function testChunkNestedArrays(): void {
		$result = $this->executeCodeSnippet("[[1, 2], [3, 4], [5, 6]]->chunk(2);");
		$this->assertEquals("[[[1, 2], [3, 4]], [[5, 6]]]", $result);
	}

	public function testChunkInvalidParameterTypeNegative(): void {
		$this->executeErrorCodeSnippet(
			"Invalid parameter type",
			"[1, 2, 3]->chunk(-3)"
		);
	}

	public function testChunkInvalidParameterTypeZero(): void {
		$this->executeErrorCodeSnippet(
			"Invalid parameter type",
			"[1, 2, 3]->chunk(0)"
		);
	}

	public function testChunkInvalidParameterType(): void {
		$this->executeErrorCodeSnippet(
			"Invalid parameter type",
			"[1, 2, 3]->chunk('invalid')"
		);
	}

}
