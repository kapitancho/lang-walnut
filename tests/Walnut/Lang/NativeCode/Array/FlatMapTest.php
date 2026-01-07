<?php

namespace Walnut\Lang\Test\NativeCode\Array;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class FlatMapTest extends CodeExecutionTestHelper {

	public function testFlatMapEmpty(): void {
		$result = $this->executeCodeSnippet("[]->flatMap(^el: Any => Array :: [el]);");
		$this->assertEquals("[]", $result);
	}

	public function testFlatMapSingleElement(): void {
		$result = $this->executeCodeSnippet("[1, 2, 3]->flatMap(^i: Integer => Array<Integer> :: [i]);");
		$this->assertEquals("[1, 2, 3]", $result);
	}

	public function testFlatMapMultipleElements(): void {
		$result = $this->executeCodeSnippet("[1, 2, 3]->flatMap(^i: Integer => Array<Integer> :: [i, i * 2]);");
		$this->assertEquals("[1, 2, 2, 4, 3, 6]", $result);
	}

	public function testFlatMapWithEmptyArrays(): void {
		$result = $this->executeCodeSnippet(
			"testFn[1, 2, 3, 4];",
			valueDeclarations: "testFn = ^arr: Array<Integer> => Array<Integer> :: arr->filter(^x: Integer => Boolean :: x % 2 == 0)->flatMap(^i: Integer => Array<Integer> :: [i]);"
		);
		$this->assertEquals("[2, 4]", $result);
	}

	public function testFlatMapReturnTypeRange(): void {
		$result = $this->executeCodeSnippet(
			"testFn[1, 2, 3];",
			valueDeclarations: "testFn = ^arr: Array<Integer, 2..3> => Array<Integer, 4..9> :: arr->flatMap(^i: Integer => Array<Integer, 2..3> :: [i, i + 1]);"
		);
		$this->assertEquals("[1, 2, 2, 3, 3, 4]", $result);
	}

	public function testFlatMapReturnTypeTuple(): void {
		$result = $this->executeCodeSnippet(
			"testFn[1, 2, 3];",
			valueDeclarations: "testFn = ^arr: Array<Integer, 2..3> => Array<Integer, 4..6> :: arr->flatMap(^i: Integer => [Integer, Integer] :: [i, i + 1]);"
		);
		$this->assertEquals("[1, 2, 2, 3, 3, 4]", $result);
	}

	public function testFlatMapResultOk(): void {
		$result = $this->executeCodeSnippet(
			"testFn[1, 2, 3];",
			valueDeclarations: "testFn = ^arr: Array<Integer, 2..3> => Result<Array<Integer, 4..9>, NotANumber> :: 
				arr->flatMap(^i: Integer => Result<Array<Integer, 2..3>, NotANumber> :: [i, (5 // i)?]);"
		);
		$this->assertEquals("[1, 5, 2, 2, 3, 1]", $result);
	}

	public function testFlatMapResultError(): void {
		$result = $this->executeCodeSnippet(
			"testFn[0, 1, 2];",
			valueDeclarations: "testFn = ^arr: Array<Integer, 2..3> => Result<Array<Integer, 4..9>, NotANumber> :: 
				arr->flatMap(^i: Integer => Result<Array<Integer, 2..3>, NotANumber> :: [i, (5 // i)?]);"
		);
		$this->assertEquals("@NotANumber", $result);
	}

	public function testFlatMapNestedArrays(): void {
		$result = $this->executeCodeSnippet("[[1, 2], [3, 4], [5]]->flatMap(^a: Array<Integer> => Array<Integer> :: a);");
		$this->assertEquals("[1, 2, 3, 4, 5]", $result);
	}

	public function testFlatMapWithStrings(): void {
		$result = $this->executeCodeSnippet(
			"['hello', 'world']->flatMap(^s: String => Array<String> :: s->chunk(1));");
		// Output may be multiline formatted, so we check the normalized content
		$this->assertStringContainsString("'h'", $result);
		$this->assertStringContainsString("'d'", $result);
	}

	public function testFlatMapInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "[1, 2, 3]->flatMap(5);");
	}

	public function testFlatMapInvalidParameterParameterType(): void {
		$this->executeErrorCodeSnippet("The parameter type Integer",
			"[1, 2, 3]->flatMap(^Boolean => Array :: [#]);");
	}

	public function testFlatMapInvalidReturnType(): void {
		$this->executeErrorCodeSnippet("The return type of the callback function must be a subtype of Array",
			"[1, 2, 3]->flatMap(^i: Integer => Integer :: i);");
	}

}
