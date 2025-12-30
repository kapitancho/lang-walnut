<?php

namespace Walnut\Lang\Test\NativeCode\Real;

use Walnut\Lang\Test\CodeExecutionTestHelper;

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
			valueDeclarations: "divide = ^p: Real<3.14..> => Real<(0..4]> :: 8.05 / p;"
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

}