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

	public function testBinaryMultiplyMinusInfinity(): void {
		$result = $this->executeCodeSnippet(
			"mul(-123);",
			valueDeclarations: "v = -3; mul = ^p: Integer<..0> => Integer<0..> :: v * p;"
		);
		$this->assertEquals("369", $result);
	}

	public function testBinaryMultiplyZero(): void {
		$result = $this->executeCodeSnippet(
			"mul(123);",
			valueDeclarations: "v = 0; mul = ^p: Integer<-400..500> => Integer<0> :: v * p;"
		);
		$this->assertEquals("0", $result);
	}

	public function testBinaryMultiplyZeroExclusive(): void {
		$result = $this->executeCodeSnippet(
			"fn[2, -3];",
			valueDeclarations: "fn = ^[a: Integer<(0..14]>, b: Integer<[-12..0)>] => Integer<[-168..0)> :: #a * #b;"
		);
		$this->assertEquals("-6", $result);
	}

	public function testBinaryMultiplyIntegerWithZero(): void {
		$result = $this->executeCodeSnippet(
			"mul(2);",
			valueDeclarations: "v = 3; mul = ^p: Integer<-5..5> => Integer<-15..15> :: v * p;"
		);
		$this->assertEquals("6", $result);
	}

	public function testBinaryMultiplyIntegerWithoutZero(): void {
		$result = $this->executeCodeSnippet(
			"mul(-2);",
			valueDeclarations: "v = 3; mul = ^p: Integer<-5..-2> => Integer<-15..-6> :: p * v;"
		);
		$this->assertEquals("-6", $result);
	}

	public function testBinaryMultiplyRealWithZero(): void {
		$result = $this->executeCodeSnippet(
			"mul(2.1);",
			valueDeclarations: "v = 3; mul = ^p: Real<-5..5.7> => Real<-15..17.1> :: v * p;"
		);
		$this->assertEquals("6.3", $result);
	}

	public function testBinaryMultiplyRealWithoutZero(): void {
		$result = $this->executeCodeSnippet(
			"mul(-3.2);",
			valueDeclarations: "v = 3; mul = ^p: Real<-5..-2.7> => Real<-15..-8.1> :: v * p;"
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

	public function testBinaryMultiplyNonZeroResult(): void {
		$result = $this->executeCodeSnippet("mulZero[-4, 2];", valueDeclarations: <<<NUT
			mulZero = ^[x: Integer<[-5..-1], [3..8]>, y: Integer[-3, 2, 11]] => Integer<[-55..0), (0..88]> :: #x * #y;
		NUT);
		$this->assertEquals("-8", $result);
	}

}