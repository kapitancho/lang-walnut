<?php

namespace Walnut\Lang\Test\NativeCode\Integer;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class BinaryMinusTest extends CodeExecutionTestHelper {

	public function testBinaryMinus(): void {
		$result = $this->executeCodeSnippet("3 - 5;");
		$this->assertEquals("-2", $result);
	}

	public function testBinaryMinusReal(): void {
		$result = $this->executeCodeSnippet("3 - 5.14;");
		$this->assertEquals("-2.14", $result);
	}

	public function testBinaryMinusSubsets(): void {
		$result = $this->executeCodeSnippet(
			"minus[5, 3];",
			valueDeclarations: "minus = ^[a: Integer[5, 2], b: Integer[2, 3]] => Integer[-1, 0, 2, 3] :: #a - #b;"
		);
		$this->assertEquals("2", $result);
	}

	public function testBinaryMinusZeroParameter(): void {
		$result = $this->executeCodeSnippet(
			"minus(0);",
			valueDeclarations: "v = 3; minus = ^p: Integer<0> => Integer<-3> :: p - v;"
		);
		$this->assertEquals("-3", $result);
	}

	public function testBinaryMinusZeroTarget(): void {
		$result = $this->executeCodeSnippet(
			"minus(0);",
			valueDeclarations: "v = 3; minus = ^p: Integer<0> => Integer<3> :: v - p;"
		);
		$this->assertEquals("3", $result);
	}

	public function testBinaryMinusInvalidParameter(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "3 - 'hello';");
	}

	public function testBinaryMinusByZeroReturnsInteger(): void {
		$result = $this->executeCodeSnippet("subZero(42);", valueDeclarations: <<<NUT
			subZero = ^x: Integer => Integer :: x - 0;
		NUT);
		$this->assertEquals("42", $result);
	}

	public function testBinaryMinusByZeroPreservesRange(): void {
		$result = $this->executeCodeSnippet("subZero(50);", valueDeclarations: <<<NUT
			subZero = ^x: Integer<1..100> => Integer<1..100> :: x - 0;
		NUT);
		$this->assertEquals("50", $result);
	}

	public function testBinaryMinusByZeroPreservesRangeSubset(): void {
		$result = $this->executeCodeSnippet("subZero[5, 0];", valueDeclarations: <<<NUT
			subZero = ^[x: Integer[3, 5, 8], y: Integer[0]] => Integer[3, 5, 8] :: #x - #y;
		NUT);
		$this->assertEquals("5", $result);
	}

	public function testBinaryPlusInfinityParameter(): void {
		$result = $this->executeCodeSnippet("minus(-20);", valueDeclarations: <<<NUT
			minus = ^x: Integer<..3> => Integer<2..> :: 5 - x;
		NUT);
		$this->assertEquals("25", $result);
	}

	public function testBinaryMinusInfinityParameter(): void {
		$result = $this->executeCodeSnippet("minus(20);", valueDeclarations: <<<NUT
			minus = ^x: Integer<3..> => Integer<..2> :: 5 - x;
		NUT);
		$this->assertEquals("-15", $result);
	}

}