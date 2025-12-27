<?php

namespace Walnut\Lang\NativeCode\Array;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class BinaryModuloTest extends CodeExecutionTestHelper {

	// Basic execution tests
	public function testBinaryModuloBasic(): void {
		$result = $this->executeCodeSnippet("[1, 2, 3, 4, 5] % 2;");
		$this->assertEquals("[5]", $result);
	}

	public function testBinaryModuloExactDivision(): void {
		$result = $this->executeCodeSnippet("[1, 2, 3, 4, 5, 6] % 3;");
		$this->assertEquals("[]", $result);
	}

	public function testBinaryModuloSizeOne(): void {
		$result = $this->executeCodeSnippet("[1, 2, 3] % 1;");
		$this->assertEquals("[]", $result);
	}

	public function testBinaryModuloSizeLargerThanArray(): void {
		$result = $this->executeCodeSnippet("[1, 2, 3] % 10;");
		$this->assertEquals("[1, 2, 3]", $result);
	}

	public function testBinaryModuloEmptyArray(): void {
		$result = $this->executeCodeSnippet("[] % 2;");
		$this->assertEquals("[]", $result);
	}

	public function testBinaryModuloSingleElement(): void {
		$result = $this->executeCodeSnippet("[42] % 5;");
		$this->assertEquals("[42]", $result);
	}

	public function testBinaryModuloStrings(): void {
		$result = $this->executeCodeSnippet("['a', 'b', 'c', 'd', 'e'] % 2;");
		$this->assertEquals("['e']", $result);
	}

	public function testBinaryModuloMixedTypes(): void {
		$result = $this->executeCodeSnippet("[1, 'two', 3.0, true, null] % 2;");
		$this->assertEquals("[null]", $result);
	}

	public function testBinaryModuloLargeArray(): void {
		$result = $this->executeCodeSnippet("[1, 2, 3, 4, 5, 6, 7, 8, 9, 10] % 3;");
		$this->assertEquals("[10]", $result);
	}

	// Type inference tests
	public function testBinaryModuloTypeBasic(): void {
		$result = $this->executeCodeSnippet(
			"chunk([[1, 2, 3, 4, 5], 2])",
			valueDeclarations: "chunk = ^[arr: Array<Integer, 5>, size: Integer<2>] => Array<Integer, 1> :: #arr % #size;"
		);
		$this->assertEquals("[5]", $result);
	}

	public function testBinaryModuloTypeMaxInfinityBoth(): void {
		$result = $this->executeCodeSnippet(
			"chunk([[1, 2, 3, 4, 5], 2])",
			valueDeclarations: "chunk = ^[arr: Array<Integer, 5..>, size: Integer<2..>] => Array<Integer> :: #arr % #size;"
		);
		$this->assertEquals("[5]", $result);
	}

	public function testBinaryModuloTypeMaxInfinity(): void {
		$result = $this->executeCodeSnippet(
			"chunk([[1, 2, 3, 4, 5], 2])",
			valueDeclarations: "chunk = ^[arr: Array<Integer, 5>, size: Integer<2..>] => Array<Integer, ..5> :: #arr % #size;"
		);
		$this->assertEquals("[5]", $result);
	}

	public function testBinaryModuloTypeMaxInfinityEmptyArray(): void {
		$result = $this->executeCodeSnippet(
			"chunk([[1, 2, 3, 4, 5], 2])",
			valueDeclarations: "chunk = ^[arr: Array<Integer, ..5>, size: Integer<2..>] => Array<Integer, ..5> :: #arr % #size;"
		);
		$this->assertEquals("[5]", $result);
	}

	public function testBinaryModuloBeyondRange(): void {
		$result = $this->executeCodeSnippet(
			"chunk([[1, 2, 3, 4, 5], 13])",
			valueDeclarations: "chunk = ^[str: Array<Integer, 3..5>, size: Integer<12..14>] => Array<Integer, 3..5> :: #str % #size;"
		);
		$this->assertEquals("[1, 2, 3, 4, 5]", $result);
	}

	public function testBinaryModuloTypeExactDivision(): void {
		$result = $this->executeCodeSnippet(
			"chunk([[1, 2, 3, 4, 5, 6], 3])",
			valueDeclarations: "chunk = ^[arr: Array<Integer, 6>, size: Integer<3>] => Array<Integer, 0> :: #arr % #size;"
		);
		$this->assertEquals("[]", $result);
	}

	public function testBinaryModuloTypeTuple(): void {
		$result = $this->executeCodeSnippet(
			"chunk([[1, 2, 'hello', false, 3.14], 3])",
			valueDeclarations: "chunk = ^[arr: [Integer, Real, String, Boolean, Real], size: Integer<3>] => [Boolean, Real] :: #arr % #size;"
		);
		$this->assertEquals("[false, 3.14]", $result);
	}

	public function testBinaryModuloTypeEmptyArray(): void {
		$result = $this->executeCodeSnippet(
			"chunk([[], 2])",
			valueDeclarations: "chunk = ^[arr: Array<Integer, 0>, size: Integer<2>] => Array<Integer, 0> :: #arr % #size;"
		);
		$this->assertEquals("[]", $result);
	}

	public function testBinaryModuloTypeRangeInput(): void {
		$result = $this->executeCodeSnippet(
			"chunk([[1, 2, 3, 4, 5], 2])",
			valueDeclarations: "chunk = ^[arr: Array<Integer, 3..10>, size: Integer<2..3>] => Array<Integer, ..2> :: #arr % #size;"
		);
		$this->assertEquals("[5]", $result);
	}

	public function testBinaryModuloTypeMinimumElements(): void {
		$result = $this->executeCodeSnippet(
			"chunk([[1, 2, 3], 2])",
			valueDeclarations: "chunk = ^[arr: Array<Integer, 3>, size: Integer<2>] => Array<Integer, 1> :: #arr % #size;"
		);
		$this->assertEquals("[3]", $result);
	}

	public function testBinaryModuloTypeLargeSizeRange(): void {
		$result = $this->executeCodeSnippet(
			"chunk([[1, 2, 3, 4], 5])",
			valueDeclarations: "chunk = ^[arr: Array<Integer, 4>, size: Integer<1..10>] => Array<Integer, ..4> :: #arr % #size;"
		);
		$this->assertEquals("[1, 2, 3, 4]", $result);
	}

	public function testBinaryModuloTypePreservesItemType(): void {
		$result = $this->executeCodeSnippet(
			"chunk([['a', 'b', 'c'], 2])",
			valueDeclarations: "chunk = ^[arr: Array<String, 3>, size: Integer<2>] => Array<String, ..1> :: #arr % #size;"
		);
		$this->assertEquals("['c']", $result);
	}

	public function testBinaryModuloTypeWithSubtype(): void {
		$result = $this->executeCodeSnippet(
			"chunk([[1, 2, 3, 4, 5], 2])",
			valueDeclarations: "chunk = ^[arr: Array<Integer<1..10>, 5>, size: Integer<2>] => Array<Integer<1..10>, ..1> :: #arr % #size;"
		);
		$this->assertEquals("[5]", $result);
	}

	// Edge cases
	public function testBinaryModuloSizeEqualsLength(): void {
		$result = $this->executeCodeSnippet("[1, 2, 3, 4, 5] % 5;");
		$this->assertEquals("[]", $result);
	}

	public function testBinaryModuloTwoElements(): void {
		$result = $this->executeCodeSnippet("[1, 2] % 1;");
		$this->assertEquals("[]", $result);
	}

	public function testBinaryModuloNestedArrays(): void {
		$result = $this->executeCodeSnippet("[[1, 2], [3, 4], [5, 6]] % 2;");
		$this->assertEquals("[[5, 6]]", $result);
	}

	public function testBinaryModuloInvalidParameterTypeNegative(): void {
		$this->executeErrorCodeSnippet(
			"Invalid parameter type",
			"[1, 2, 3] % -3;"
		);
	}

	public function testBinaryModuloInvalidParameterTypeZero(): void {
		$this->executeErrorCodeSnippet(
			"Invalid parameter type",
			"[1, 2, 3] % 0;"
		);
	}

	public function testBinaryModuloInvalidParameterType(): void {
		$this->executeErrorCodeSnippet(
			"Invalid parameter type",
			"[1, 2, 3] % 'invalid';"
		);
	}

}
