<?php

namespace Walnut\Lang\Test\NativeCode\Array;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class ReduceTest extends CodeExecutionTestHelper {

	// Basic execution tests
	public function testReduceSumIntegers(): void {
		$result = $this->executeCodeSnippet(
			"[1, 2, 3, 4, 5]->reduce[reducer: ^[result: Integer, item: Integer] => Integer :: #result + #item, initial: 0];"
		);
		$this->assertEquals("15", $result);
	}

	public function testReduceMultiplyIntegers(): void {
		$result = $this->executeCodeSnippet(
			"[2, 3, 4]->reduce[reducer: ^[result: Integer, item: Integer] => Integer :: #result * #item, initial: 1];"
		);
		$this->assertEquals("24", $result);
	}

	public function testReduceConcatenateStrings(): void {
		$result = $this->executeCodeSnippet(
			"['a', 'b', 'c']->reduce[reducer: ^[result: String, item: String] => String :: #result + #item, initial: ''];"
		);
		$this->assertEquals("'abc'", $result);
	}

	public function testReduceIntegersToString(): void {
		$result = $this->executeCodeSnippet(
			"[1, 2, 3]->reduce[reducer: ^[result: String, item: Integer] => String :: #result + #item->asString, initial: ''];"
		);
		$this->assertEquals("'123'", $result);
	}

	public function testReduceCountElements(): void {
		$result = $this->executeCodeSnippet(
			"[10, 20, 30, 40]->reduce[reducer: ^[result: Integer, item: Integer] => Integer :: #result + 1, initial: 0];"
		);
		$this->assertEquals("4", $result);
	}

	public function testReduceFindMaximum(): void {
		$result = $this->executeCodeSnippet(
			"[3, 7, 2, 9, 1, 5]->reduce[reducer: ^[result: Integer, item: Integer] => Integer :: #result + #item, initial: 0];"
		);
		$this->assertEquals("27", $result);
	}

	public function testReduceFindMinimum(): void {
		$result = $this->executeCodeSnippet(
			"[10, 20, 30]->reduce[reducer: ^[result: Integer, item: Integer] => Integer :: #result * #item, initial: 1];"
		);
		$this->assertEquals("6000", $result);
	}

	public function testReduceEmptyArray(): void {
		$result = $this->executeCodeSnippet(
			"[]->reduce[reducer: ^[result: Integer, item: Integer] => Integer :: #result + #item, initial: 42];"
		);
		$this->assertEquals("42", $result);
	}

	public function testReduceSingleElement(): void {
		$result = $this->executeCodeSnippet(
			"[5]->reduce[reducer: ^[result: Integer, item: Integer] => Integer :: #result + #item, initial: 10];"
		);
		$this->assertEquals("15", $result);
	}

	public function testReduceWithSubtraction(): void {
		$result = $this->executeCodeSnippet(
			"[1, 2, 3]->reduce[reducer: ^[result: Integer, item: Integer] => Integer :: #result - #item, initial: 10];"
		);
		$this->assertEquals("4", $result); // 10 - 1 - 2 - 3 = 4
	}

	public function testReduceWithDivision(): void {
		$result = $this->executeCodeSnippet(
			"[2, 5]->reduce[reducer: ^[result: Integer, item: Integer] => Integer :: #result - #item, initial: 20];"
		);
		$this->assertEquals("13", $result); // 20 - 2 - 5 = 13
	}

	public function testReduceStringsWithSeparator(): void {
		$result = $this->executeCodeSnippet(
			"['apple', 'banana']->reduce[reducer: ^[result: String, item: String] => String :: #result + '-' + #item, initial: 'fruit'];"
		);
		$this->assertEquals("'fruit-apple-banana'", $result);
	}

	public function testReduceBuildArray(): void {
		$result = $this->executeCodeSnippet(
			"[1, 2, 3]->reduce[reducer: ^[result: String, item: Integer] => String :: #result + '[' + #item->asString + ']', initial: 'Array: '];"
		);
		$this->assertEquals("'Array: [1][2][3]'", $result);
	}

	public function testReduceMixedTypes(): void {
		$result = $this->executeCodeSnippet(
			"[1, 2, 3, 4, 5]->reduce[reducer: ^[result: Real, item: Integer] => Real :: #result + #item->asReal, initial: 0.5];"
		);
		$this->assertEquals("15.5", $result);
	}

	// Type inference tests
	public function testReduceTypeInteger(): void {
		$result = $this->executeCodeSnippet(
			"reduceSum[1, 2, 3, 4, 5]",
			valueDeclarations: "reduceSum = ^arr: Array<Integer, 5> => Integer :: arr->reduce[reducer: ^[result: Integer, item: Integer] => Integer :: #result + #item, initial: 0];"
		);
		$this->assertEquals("15", $result);
	}

	public function testReduceTypeString(): void {
		$result = $this->executeCodeSnippet(
			"reduceConcat['a', 'b', 'c']",
			valueDeclarations: "reduceConcat = ^arr: Array<String, 3> => String :: arr->reduce[reducer: ^[result: String, item: String] => String :: #result + #item, initial: ''];"
		);
		$this->assertEquals("'abc'", $result);
	}

	public function testReduceTypeTransformIntegerToString(): void {
		$result = $this->executeCodeSnippet(
			"transform[1, 2, 3]",
			valueDeclarations: "transform = ^arr: Array<Integer, 3> => String :: arr->reduce[reducer: ^[result: String, item: Integer] => String :: #result + #item->asString, initial: 'Numbers: '];"
		);
		$this->assertEquals("'Numbers: 123'", $result);
	}

	public function testReduceTypeTransformStringToInteger(): void {
		$result = $this->executeCodeSnippet(
			"countChars['hello', 'world']",
			valueDeclarations: "countChars = ^arr: Array<String, 2> => Integer :: arr->reduce[reducer: ^[result: Integer, item: String] => Integer :: #result + #item->length, initial: 0];"
		);
		$this->assertEquals("10", $result);
	}

	public function testReduceTypeTransformStringToIntegerResultOk(): void {
		$result = $this->executeCodeSnippet(
			"countChars['3', '7', '-2']",
			valueDeclarations: "countChars = ^arr: Array<String, 3> => Result<Integer, NotANumber> :: arr->reduce[reducer: ^[result: Integer, item: String] => Result<Integer, NotANumber> :: #result + #item->asInteger?, initial: 0];"
		);
		$this->assertEquals("8", $result);
	}

	public function testReduceTypeTransformStringToIntegerResultError(): void {
		$result = $this->executeCodeSnippet(
			"countChars['3', 'hello', '-2']",
			valueDeclarations: "countChars = ^arr: Array<String, 3> => Result<Integer, NotANumber> :: arr->reduce[reducer: ^[result: Integer, item: String] => Result<Integer, NotANumber> :: #result + #item->asInteger?, initial: 0];"
		);
		$this->assertEquals("@NotANumber", $result);
	}

	public function testReduceTypeEmptyArray(): void {
		$result = $this->executeCodeSnippet(
			"reduceEmpty([])",
			valueDeclarations: "reduceEmpty = ^arr: Array<Integer, 0> => Integer :: arr->reduce[reducer: ^[result: Integer, item: Integer] => Integer :: #result + #item, initial: 100];"
		);
		$this->assertEquals("100", $result);
	}

	public function testReduceTypeWithRange(): void {
		$result = $this->executeCodeSnippet(
			"reduceRange[5, 10, 15]",
			valueDeclarations: "reduceRange = ^arr: Array<Integer<1..20>, 3> => Integer :: arr->reduce[reducer: ^[result: Integer, item: Integer<1..20>] => Integer :: #result + #item, initial: 0];"
		);
		$this->assertEquals("30", $result);
	}

	public function testReduceTypeRealNumbers(): void {
		$result = $this->executeCodeSnippet(
			"reduceReals[1.5, 2.5, 3.5]",
			valueDeclarations: "reduceReals = ^arr: Array<Real, 3> => Real :: arr->reduce[reducer: ^[result: Real, item: Real] => Real :: #result + #item, initial: 0.0];"
		);
		$this->assertEquals("7.5", $result);
	}

	// Edge cases
	public function testReduceLargeArray(): void {
		$result = $this->executeCodeSnippet(
			"[1, 2, 3, 4, 5, 6, 7, 8, 9, 10]->reduce[reducer: ^[result: Integer, item: Integer] => Integer :: #result + #item, initial: 0];"
		);
		$this->assertEquals("55", $result);
	}

	public function testReduceNegativeNumbers(): void {
		$result = $this->executeCodeSnippet(
			"[-1, -2, -3]->reduce[reducer: ^[result: Integer, item: Integer] => Integer :: #result + #item, initial: 0];"
		);
		$this->assertEquals("-6", $result);
	}

	public function testReduceAlternatingOperation(): void {
		$result = $this->executeCodeSnippet(
			"[1, 2, 3, 4]->reduce[reducer: ^[result: Integer, item: Integer] => Integer :: #result * 10 + #item, initial: 0];"
		);
		$this->assertEquals("1234", $result);
	}

	// Error tests
	public function testReduceInvalidParameterNotTuple(): void {
		$this->executeErrorCodeSnippet(
			"Parameter must be a record with 'reducer' and 'initial' fields",
			"[1, 2, 3]->reduce(5)"
		);
	}

	public function testReduceInvalidParameterNotFunction(): void {
		$this->executeErrorCodeSnippet(
			"Parameter must be a record with 'reducer' and 'initial' fields",
			"[1, 2, 3]->reduce[reducer: 5, initial: 0]"
		);
	}

	public function testReduceInvalidItemTypeMismatch(): void {
		$this->executeErrorCodeSnippet(
			"Parameter must be a record",
			"reduceWrong[1, 2, 3]",
			valueDeclarations: "reduceWrong = ^arr: Array<Integer, 3> => Integer :: arr->reduce[reducer: ^[result: Integer, item: String] => Integer :: #result + #item->length, initial: 0];"
		);
	}

	public function testReduceInvalidReturnTypeMismatch(): void {
		$this->executeErrorCodeSnippet(
			"Reducer return type",
			"reduceWrong([1, 2, 3])",
			valueDeclarations: "reduceWrong = ^arr: Array<Integer, 3> => Integer :: arr->reduce[reducer: ^[result: Integer, item: Integer] => String :: #result->asString, initial: 0];"
		);
	}

	public function testReduceInvalidInitialTypeMismatch(): void {
		$this->executeErrorCodeSnippet(
			"Initial value type",
			"reduceWrong([1, 2, 3])",
			valueDeclarations: "reduceWrong = ^arr: Array<Integer, 3> => Integer :: arr->reduce[reducer: ^[result: Integer, item: Integer] => Integer :: #result + #item, initial: 'not an integer'];"
		);
	}

}
