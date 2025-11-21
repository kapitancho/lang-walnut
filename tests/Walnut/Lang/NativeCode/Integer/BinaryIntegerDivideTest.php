<?php

namespace Walnut\Lang\Test\NativeCode\Integer;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class BinaryIntegerDivideTest extends CodeExecutionTestHelper {

	public function testBinaryIntegerDivide(): void {
		$result = $this->executeCodeSnippet("3 // 2;");
		$this->assertEquals("1", $result);
	}

	public function testBinaryIntegerDivideZero(): void {
		$result = $this->executeCodeSnippet("3 // 0;");
		$this->assertEquals("@NotANumber", $result);
	}

	public function testBinaryIntegerDivideInvalidParameter(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "3 // 1.4;");
	}

	public function testBinaryIntegerDivideByOneReturnsInteger(): void {
		$result = $this->executeCodeSnippet("divOne(42);", valueDeclarations: <<<NUT
			divOne = ^x: Integer => Integer :: x // 1;
		NUT);
		$this->assertEquals("42", $result);
	}

	public function testBinaryIntegerDivideByOnePreservesRange(): void {
		$result = $this->executeCodeSnippet("divOne(50);", valueDeclarations: <<<NUT
			divOne = ^x: Integer<1..100> => Integer<1..100> :: x // 1;
		NUT);
		$this->assertEquals("50", $result);
	}

	public function testBinaryIntegerDividePositiveTargetFinite(): void {
		$result = $this->executeCodeSnippet(
			"divide(5);",
			valueDeclarations: "divide = ^p: Integer<3..10> => Integer<1..3> :: p // 3;"
		);
		$this->assertEquals("1", $result);
	}

	public function testBinaryIntegerDivideByOnePreservesRangeSubset(): void {
		$result = $this->executeCodeSnippet("divOne[5, 1];", valueDeclarations: <<<NUT
			divOne = ^[x: Integer[3, 5, 8], y: Integer[1]] => Integer[3, 5, 8] :: #x // #y;
		NUT);
		$this->assertEquals("5", $result);
	}

}