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

	public function testBinaryMultiplyZeroParameter(): void {
		$result = $this->executeCodeSnippet(
			"mul(0);",
			valueDeclarations: "v = 3; mul = ^p: Integer<0> => Integer<0> :: p * v;"
		);
		$this->assertEquals("0", $result);
	}

	public function testBinaryMultiplyZeroTarget(): void {
		$result = $this->executeCodeSnippet(
			"mul(0);",
			valueDeclarations: "v = 3; mul = ^p: Integer<0> => Integer<0> :: v * p;"
		);
		$this->assertEquals("0", $result);
	}

	public function testBinaryMultiplyOneParameter(): void {
		$result = $this->executeCodeSnippet(
			"mul(1);",
			valueDeclarations: "v = 3; mul = ^p: Integer<1> => Integer<3> :: p * v;"
		);
		$this->assertEquals("3", $result);
	}

	public function testBinaryMultiplyOneTarget(): void {
		$result = $this->executeCodeSnippet(
			"mul(1);",
			valueDeclarations: "v = 3; mul = ^p: Integer<1> => Integer<3> :: v * p;"
		);
		$this->assertEquals("3", $result);
	}

	public function testBinaryMultiplyPlusInfinity(): void {
		$result = $this->executeCodeSnippet(
			"mul(123);",
			valueDeclarations: "v = 3; mul = ^p: Integer<0..> => Integer<0..> :: v * p;"
		);
		$this->assertEquals("369", $result);
	}

	//TODO - refined Integer in the following tests.
	public function testBinaryMultiplyIntegerWithZero(): void {
		$result = $this->executeCodeSnippet(
			"mul(2);",
			valueDeclarations: "v = 3; mul = ^p: Integer<-5..5> => Integer :: v * p;"
		);
		$this->assertEquals("6", $result);
	}

	//TODO - refined Integer in the following tests.
	public function testBinaryMultiplyIntegerWithoutZero(): void {
		$result = $this->executeCodeSnippet(
			"mul(-2);",
			valueDeclarations: "v = 3; mul = ^p: Integer<-5..-2> => NonZeroInteger :: p * v;"
		);
		$this->assertEquals("-6", $result);
	}

	//TODO - refined Integer in the following tests.
	public function testBinaryMultiplyRealWithZero(): void {
		$result = $this->executeCodeSnippet(
			"mul(2.1);",
			valueDeclarations: "v = 3; mul = ^p: Real<-5..5.7> => Real :: v * p;"
		);
		$this->assertEquals("6.3", $result);
	}

	//TODO - refined Integer in the following tests.
	public function testBinaryMultiplyRealWithoutZero(): void {
		$result = $this->executeCodeSnippet(
			"mul(-3.2);",
			valueDeclarations: "v = 3; mul = ^p: Real<-5..-2.7> => NonZeroReal :: v * p;"
		);
		$this->assertEquals("-9.6", $result);
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