<?php

namespace Walnut\Lang\Test\NativeCode\Integer;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class DigitsTest extends CodeExecutionTestHelper {

	public function testDigitsSingleDigit(): void {
		$result = $this->executeCodeSnippet("5->digits;");
		$this->assertEquals("[5]", $result);
	}

	public function testDigitsZero(): void {
		$result = $this->executeCodeSnippet("0->digits;");
		$this->assertEquals("[0]", $result);
	}

	public function testDigitsTwoDigits(): void {
		$result = $this->executeCodeSnippet("42->digits;");
		$this->assertEquals("[4, 2]", $result);
	}

	public function testDigitsThreeDigits(): void {
		$result = $this->executeCodeSnippet("123->digits;");
		$this->assertEquals("[1, 2, 3]", $result);
	}

	public function testDigitsWithZeros(): void {
		$result = $this->executeCodeSnippet("1000->digits;");
		$this->assertEquals("[1, 0, 0, 0]", $result);
	}

	public function testDigitsAllSameDigit(): void {
		$result = $this->executeCodeSnippet("777->digits;");
		$this->assertEquals("[7, 7, 7]", $result);
	}

	public function testDigitsLargeNumber(): void {
		$result = $this->executeCodeSnippet("987654321->digits;");
		$this->assertEquals("[9, 8, 7, 6, 5, 4, 3, 2, 1]", $result);
	}

	public function testDigitsReturnType(): void {
		$result = $this->executeCodeSnippet(
			"digitsFn(2);",
			valueDeclarations: "digitsFn = ^v: Integer<0..9> => Array<Integer<0..9>, 1> :: v->digits;"
		);
		$this->assertEquals("[2]", $result);
	}

	public function testDigitsReturnType1000(): void {
		$result = $this->executeCodeSnippet(
			"digitsFn(123);",
			valueDeclarations: "digitsFn = ^v: Integer<0..9999> => Array<Integer<0..9999>, 1..4> :: v->digits;"
		);
		$this->assertEquals("[1, 2, 3]", $result);
	}

	public function testDigitsReturnTypeInfinity(): void {
		$result = $this->executeCodeSnippet(
			"digitsFn(123);",
			valueDeclarations: "digitsFn = ^v: Integer<0..> => Array<Integer<0..>, 1..> :: v->digits;"
		);
		$this->assertEquals("[1, 2, 3]", $result);
	}

	public function testDigitsInvalidTargetType(): void {
		$this->executeErrorCodeSnippet(
			"Invalid target type: Integer<-50..50>",
			"digitsFn(5);",
			valueDeclarations: "digitsFn = ^v: Integer<-50..50> => Any :: v->digits;"
		);
	}

}
