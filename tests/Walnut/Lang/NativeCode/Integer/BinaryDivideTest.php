<?php

namespace Walnut\Lang\Test\NativeCode\Integer;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class BinaryDivideTest extends CodeExecutionTestHelper {

	public function testBinaryDivide(): void {
		$result = $this->executeCodeSnippet("3 / 2;");
		$this->assertEquals("1.5", $result);
	}

	public function testBinaryDivideReal(): void {
		$result = $this->executeCodeSnippet("3 / 1.5;");
		$this->assertEquals("2", $result);
	}

	public function testBinaryDivideZero(): void {
		$result = $this->executeCodeSnippet("3 / 0;");
		$this->assertEquals("@NotANumber", $result);
	}

	public function testBinaryDivideOneParameter(): void {
		$result = $this->executeCodeSnippet(
			"divide(3);",
			valueDeclarations: "divide = ^p: Integer<-1..3> => Integer<-1..3> :: p / 1;"
		);
		$this->assertEquals("3", $result);
	}

	public function testBinaryDivideSubsets(): void {
		$result = $this->executeCodeSnippet(
			"divide[5, -1];",
			valueDeclarations: "divide = ^[a: Integer[5, 2], b: Integer[2, -1]] => Real[-5, -2, 1, 2.5] :: #a / #b;"
		);
		$this->assertEquals("-5", $result);
	}

	public function testBinaryDivideSubsetsWithZero(): void {
		$result = $this->executeCodeSnippet(
			"divide[5, 0];",
			valueDeclarations: "divide = ^[a: Integer[5, 2], b: Integer[2, -1, 0]] => Result<Real[-5, -2, 1, 2.5], NotANumber> :: #a / #b;"
		);
		$this->assertEquals("@NotANumber", $result);
	}

	public function testBinaryDivideOneParameterInteger(): void {
		$result = $this->executeCodeSnippet(
			"divide(3);",
			valueDeclarations: "divide = ^p: Integer<-1..3> => Integer<-1..3> :: p / 1;"
		);
		$this->assertEquals("3", $result);
	}

	public function testBinaryDividePositiveTargetFinite(): void {
		$result = $this->executeCodeSnippet(
			"divide(5);",
			valueDeclarations: "divide = ^p: Integer<3..10> => Real<[1.2..4]> :: p / 2.5;"
		);
		$this->assertEquals("2", $result);
	}

	public function testBinaryDivideInvalidParameter(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "3 / 'hello';");
	}

	public function testBinaryDivideReturnTypeOk(): void {
		$result = $this->executeCodeSnippet("div[3, 2];", valueDeclarations: <<<NUT
			div = ^[a: Integer, b: Integer<(..0), (0..)>] => Real :: #a / #b;
		NUT);
		$this->assertEquals("1.5", $result);
	}

	public function testBinaryDivideByOneReturnsInteger(): void {
		$result = $this->executeCodeSnippet("divByOne(42);", valueDeclarations: <<<NUT
			divByOne = ^x: Integer => Integer :: x / 1;
		NUT);
		$this->assertEquals("42", $result);
	}

	public function testBinaryDivideByOnePreservesRange(): void {
		$result = $this->executeCodeSnippet("divByOne(50);", valueDeclarations: <<<NUT
			divByOne = ^x: Integer<1..100> => Integer<1..100> :: x / 1;
		NUT);
		$this->assertEquals("50", $result);
	}

	public function testBinaryDivideByOnePreservesRangeSubset(): void {
		$result = $this->executeCodeSnippet("divByOne[5, 1];", valueDeclarations: <<<NUT
			divByOne = ^[x: Integer[3, 5, 8], y: Integer[1]] => Integer[3, 5, 8] :: #x / #y;
		NUT);
		$this->assertEquals("5", $result);
	}

	public function testBinaryDivideInfinityParameter(): void {
		$result = $this->executeCodeSnippet("div(20);", valueDeclarations: <<<NUT
			div = ^x: Integer<3..> => Real<(0..1.67)> :: 5 / x;
		NUT);
		$this->assertEquals("0.25", $result);
	}

	public function testBinaryDivideNonZeroTargetFiniteParameter(): void {
		$result = $this->executeCodeSnippet("div[x: 4, y: 2];", valueDeclarations: <<<NUT
			div = ^[x: Integer<3..>, y: Real<-5..5>] => Result<NonZeroReal, NotANumber> :: #x / #y;
		NUT);
		$this->assertEquals("2", $result);
	}

	public function testBinaryDivideNonZeroTargetInfiniteParameter(): void {
		$result = $this->executeCodeSnippet("div[x: 4, y: 2];", valueDeclarations: <<<NUT
			div = ^[x: Integer<3..>, y: Real] => Result<NonZeroReal, NotANumber> :: #x / #y;
		NUT);
		$this->assertEquals("2", $result);
	}

}