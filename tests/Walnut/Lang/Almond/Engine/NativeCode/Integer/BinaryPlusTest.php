<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Integer;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class BinaryPlusTest extends CodeExecutionTestHelper {

	public function testBinaryPlus(): void {
		$result = $this->executeCodeSnippet("3 + 5;");
		$this->assertEquals("8", $result);
	}

	public function testBinaryPlusReal(): void {
		$result = $this->executeCodeSnippet("3 + 5.14;");
		$this->assertEquals("8.14", $result);
	}

	public function testBinaryPlusSubsets(): void {
		$result = $this->executeCodeSnippet(
			"plus[5, 3];",
			valueDeclarations: "plus = ^[a: Integer[5, 2], b: Integer[2, 3]] => Integer[4, 5, 7, 8] :: #a + #b;"
		);
		$this->assertEquals("8", $result);
	}

	public function testBinaryPlusZeroParameter(): void {
		$result = $this->executeCodeSnippet(
			"plus(0);",
			valueDeclarations: "v = 3; plus = ^p: Integer<0> => Integer<3> :: p + v;"
		);
		$this->assertEquals("3", $result);
	}

	public function testBinaryPlusZeroTarget(): void {
		$result = $this->executeCodeSnippet(
			"plus(0);",
			valueDeclarations: "v = 3; plus = ^p: Integer<0> => Integer<3> :: v + p;"
		);
		$this->assertEquals("3", $result);
	}

	public function testBinaryPlusInvalidParameter(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "3 + 'hello';");
	}

	public function testBinaryPlusByZeroReturnsInteger(): void {
		$result = $this->executeCodeSnippet("addZero(42);", valueDeclarations: <<<NUT
			addZero = ^x: Integer => Integer :: x + 0;
		NUT);
		$this->assertEquals("42", $result);
	}

	public function testBinaryPlusByZeroPreservesRange(): void {
		$result = $this->executeCodeSnippet("addZero(50);", valueDeclarations: <<<NUT
			addZero = ^x: Integer<1..100> => Integer<1..100> :: x + 0;
		NUT);
		$this->assertEquals("50", $result);
	}

	public function testBinaryPlusByZeroPreservesRangeSubset(): void {
		$result = $this->executeCodeSnippet("addZero[5, 0];", valueDeclarations: <<<NUT
			addZero = ^[x: Integer[3, 5, 8], y: Integer[0]] => Integer[3, 5, 8] :: #x + #y;
		NUT);
		$this->assertEquals("5", $result);
	}

	public function testBinaryPlusZeroByValueReturnsInteger(): void {
		$result = $this->executeCodeSnippet("addToZero(42);", valueDeclarations: <<<NUT
			addToZero = ^x: Integer => Integer :: 0 + x;
		NUT);
		$this->assertEquals("42", $result);
	}

}