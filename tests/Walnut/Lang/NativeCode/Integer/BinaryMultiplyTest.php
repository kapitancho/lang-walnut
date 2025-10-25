<?php

namespace Walnut\Lang\Test\NativeCode\Integer;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class BinaryMultiplyTest extends CodeExecutionTestHelper {

	public function testBinaryMultiply(): void {
		$result = $this->executeCodeSnippet("3 * 5;");
		$this->assertEquals("15", $result);
	}

	public function testBinaryMultiplyReal(): void {
		$result = $this->executeCodeSnippet("3 * 5.14;");
		$this->assertEquals("15.42", $result);
	}

	public function testBinaryMultiplyInvalidParameter(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "3 * 'hello';");
	}

	public function testBinaryMultiplyByOneReturnsInteger(): void {
		$result = $this->executeCodeSnippet("mulOne(42);", valueDeclarations: <<<NUT
			mulOne = ^x: Integer => Integer :: x * 1;
		NUT);
		$this->assertEquals("42", $result);
	}

	public function testBinaryMultiplyByOnePreservesRange(): void {
		$result = $this->executeCodeSnippet("mulOne(50);", valueDeclarations: <<<NUT
			mulOne = ^x: Integer<1..100> => Integer<1..100> :: x * 1;
		NUT);
		$this->assertEquals("50", $result);
	}

	public function testBinaryMultiplyByOnePreservesRangeSubset(): void {
		$result = $this->executeCodeSnippet("mulOne[5, 1];", valueDeclarations: <<<NUT
			mulOne = ^[x: Integer[3, 5, 8], y: Integer[1]] => Integer[3, 5, 8] :: #x * #y;
		NUT);
		$this->assertEquals("5", $result);
	}

	public function testBinaryMultiplyOneByValueReturnsInteger(): void {
		$result = $this->executeCodeSnippet("mulByOne(42);", valueDeclarations: <<<NUT
			mulByOne = ^x: Integer => Integer :: 1 * x;
		NUT);
		$this->assertEquals("42", $result);
	}

	public function testBinaryMultiplyByZeroReturnsZero(): void {
		$result = $this->executeCodeSnippet("mulZero(42);", valueDeclarations: <<<NUT
			mulZero = ^x: Integer => Integer<0..0> :: x * 0;
		NUT);
		$this->assertEquals("0", $result);
	}

	public function testBinaryMultiplyByZeroPreservesZeroType(): void {
		$result = $this->executeCodeSnippet("mulZero[7, 0];", valueDeclarations: <<<NUT
			mulZero = ^[x: Integer<1..100>, y: Integer[0]] => Integer[0] :: #x * #y;
		NUT);
		$this->assertEquals("0", $result);
	}

}