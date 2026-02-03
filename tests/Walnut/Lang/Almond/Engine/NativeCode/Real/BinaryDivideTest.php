<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Real;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class BinaryDivideTest extends CodeExecutionTestHelper {

	public function testBinaryDivide(): void {
		$result = $this->executeCodeSnippet("3.6 / 2;");
		$this->assertEquals("1.8", $result);
	}

	public function testBinaryDivideReal(): void {
		$result = $this->executeCodeSnippet("3.6 / 1.8;");
		$this->assertEquals("2", $result);
	}

	public function testBinaryDivideZero(): void {
		$result = $this->executeCodeSnippet("3.6 / 0;");
		$this->assertEquals("@NotANumber", $result);
	}

	public function testBinaryDivideOneParameter(): void {
		$result = $this->executeCodeSnippet(
			"divide(3.14);",
			valueDeclarations: "divide = ^p: Real<-1..3.14> => Real<-1..3.14> :: p / 1;"
		);
		$this->assertEquals("3.14", $result);
	}

	public function testBinaryDivideOneParameterInteger(): void {
		$result = $this->executeCodeSnippet(
			"divide(3);",
			valueDeclarations: "divide = ^p: Real<-1..3.14> => Real<-1..3.14> :: p / 1;"
		);
		$this->assertEquals("3", $result);
	}

	public function testBinaryDivideSubsets(): void {
		$result = $this->executeCodeSnippet(
			"divide[5.4, -1];",
			valueDeclarations: "divide = ^[a: Real[5.4, 2], b: Integer[2, -1]] => Real[-5.4, -2, 1, 2.7] :: #a / #b;"
		);
		$this->assertEquals("-5.4", $result);
	}

	public function testBinaryDivideSubsetsWithZero(): void {
		$result = $this->executeCodeSnippet(
			"divide[5.4, -0];",
			valueDeclarations: "divide = ^[a: Real[5.4, 2], b: Real[2.5, -1, 0]] => Result<Real[-5.4, -2, 0.8, 2.16], NotANumber> :: #a / #b;"
		);
		$this->assertEquals("@NotANumber", $result);
	}

	public function testBinaryDividePositiveTargetFiniteBothPositive(): void {
		$result = $this->executeCodeSnippet(
			"divide(5.75);",
			valueDeclarations: "divide = ^p: Real<3.14..10> => Real<[1.256..4]> :: p / 2.5;"
		);
		$this->assertEquals("2.3", $result);
	}

	public function testBinaryDividePositiveTargetFiniteBothNegative(): void {
		$result = $this->executeCodeSnippet(
			"divide(-5.75);",
			valueDeclarations: "divide = ^p: Real<-10..-3.14> => Real<[1.256..4]> :: p / -2.5;"
		);
		$this->assertEquals("2.3", $result);
	}

	public function testBinaryDividePositiveTargetFiniteParameterNegative(): void {
		$result = $this->executeCodeSnippet(
			"divide(5.75);",
			valueDeclarations: "divide = ^p: Real<3.14..10> => Real<[-4..-1.256]> :: p / -2.5;"
		);
		$this->assertEquals("-2.3", $result);
	}

	public function testBinaryDividePositiveTargetFiniteTargetNegative(): void {
		$result = $this->executeCodeSnippet(
			"divide(-5.75);",
			valueDeclarations: "divide = ^p: Real<-10..-3.14> => Real<[-4..-1.256]> :: p / 2.5;"
		);
		$this->assertEquals("-2.3", $result);
	}

	public function testBinaryDividePositiveParameterFinite(): void {
		$result = $this->executeCodeSnippet(
			"divide(6.28);",
			valueDeclarations: "divide = ^p: Real<3.925..19.625> => Real<0.4..2> :: 7.85 / p;"
		);
		$this->assertEquals("1.25", $result);
	}

	public function testBinaryDividePositiveParameterInfinite(): void {
		$result = $this->executeCodeSnippet(
			"divide(3.5);",
			valueDeclarations: "divide = ^p: Real<3.14..> => Real<(0..2.57]> :: 8.05 / p;"
		);
		$this->assertEquals("2.3", $result);
	}

	public function testBinaryDivideInvalidParameter(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "3.6 / 'hello';");
	}

	public function testBinaryDivideReturnTypeOk(): void {
		$result = $this->executeCodeSnippet("div[3.6, 1.5];", valueDeclarations: <<<NUT
			div = ^[a: Real, b: Real<(..0), (0..)>] => Real :: #a / #b;
		NUT);
		$this->assertEquals("2.4", $result);
	}

	public function testBinaryDivideReturnTypeResultOk(): void {
		$result = $this->executeCodeSnippet("div[3.6, 1.5];", valueDeclarations: <<<NUT
			div = ^[a: Real, b: Real] => Result<Real, NotANumber> :: #a / #b;
		NUT);
		$this->assertEquals("2.4", $result);
	}

	public function testBinaryDivideReturnTypeOkNonZero(): void {
		$result = $this->executeCodeSnippet("div[-3.6, 1.5];", valueDeclarations: <<<NUT
			div = ^[a: Real<..-3>, b: Real<(..0), (0..)>] => Real :: #a / #b;
		NUT);
		$this->assertEquals("-2.4", $result);
	}

	public function testBinaryDivideReturnTypeResultOkNonZero(): void {
		$result = $this->executeCodeSnippet("div[3.6, 1.5];", valueDeclarations: <<<NUT
			div = ^[a: Real<3..>, b: Real] => Result<Real, NotANumber> :: #a / #b;
		NUT);
		$this->assertEquals("2.4", $result);
	}

	public function testBinaryDivideReturnTypeTargetWithZeroPositiveParameter(): void {
		$result = $this->executeCodeSnippet("div[7.2, 2.5];", valueDeclarations: <<<NUT
			div = ^[a: Real<0..>, b: Real<2..4>] => Real<0..> :: #a / #b;
		NUT);
		$this->assertEquals("2.88", $result);
	}

	public function testBinaryDivideReturnTypeTargetPlusInfinityPositiveParameter(): void {
		$result = $this->executeCodeSnippet("div[7.2, 2.5];", valueDeclarations: <<<NUT
			div = ^[a: Real<4..>, b: Real<2..4>] => Real<1..> :: #a / #b;
		NUT);
		$this->assertEquals("2.88", $result);
	}

	public function testBinaryDivideReturnTypeTargetPlusInfinityNegativeParameter(): void {
		$result = $this->executeCodeSnippet("div[7.2, -2.5];", valueDeclarations: <<<NUT
			div = ^[a: Real<4..>, b: Real<-4..-2>] => Real<..-1> :: #a / #b;
		NUT);
		$this->assertEquals("-2.88", $result);
	}

}