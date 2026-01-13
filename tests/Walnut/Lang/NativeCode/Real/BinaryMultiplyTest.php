<?php

namespace Walnut\Lang\Test\NativeCode\Real;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class BinaryMultiplyTest extends CodeExecutionTestHelper {

	public function testBinaryMultiply(): void {
		$result = $this->executeCodeSnippet("3.2 * 5;");
		$this->assertEquals("16", $result);
	}

	public function testBinaryMultiplyReal(): void {
		$result = $this->executeCodeSnippet("3.2 * 5.14;");
		$this->assertEquals("16.448", $result);
	}

	public function testBinaryMultiplySubsets(): void {
		$result = $this->executeCodeSnippet(
			"multiply[5.4, 3];",
			valueDeclarations: "multiply = ^[a: Real[5.4, 2], b: Integer[2, 3]] => Real[4, 6, 10.8, 16.2] :: #a * #b;"
		);
		$this->assertEquals("16.2", $result);
	}

	public function testBinaryMultiplyZeroParameter(): void {
		$result = $this->executeCodeSnippet(
			"mul(0);",
			valueDeclarations: "v = 3.14; mul = ^p: Real<0> => Real<0> :: p * v;"
		);
		$this->assertEquals("0", $result);
	}

	public function testBinaryMultiplyZeroTarget(): void {
		$result = $this->executeCodeSnippet(
			"mul(0);",
			valueDeclarations: "v = 3.14; mul = ^p: Real<0> => Real<0> :: v * p;"
		);
		$this->assertEquals("0", $result);
	}

	public function testBinaryMultiplyOneParameter(): void {
		$result = $this->executeCodeSnippet(
			"mul(1);",
			valueDeclarations: "v = 3.14; mul = ^p: Real<1> => Real<3.14> :: p * v;"
		);
		$this->assertEquals("3.14", $result);
	}

	public function testBinaryMultiplyOneTarget(): void {
		$result = $this->executeCodeSnippet(
			"mul(1);",
			valueDeclarations: "v = 3.14; mul = ^p: Real<1> => Real<3.14> :: v * p;"
		);
		$this->assertEquals("3.14", $result);
	}

	public function testBinaryMultiplyNonZeroResult(): void {
		$result = $this->executeCodeSnippet("mulZero[-4, 2];", valueDeclarations: <<<NUT
			mulZero = ^[x: Real<[-5.1..-0.2], [3..8]>, y: Real[-3, 2, 11.4]] => Real<[-58.14..0), (0..91.2]> :: #x * #y;
		NUT);
		$this->assertEquals("-8", $result);
	}

	public function testBinaryMultiplyInvalidParameter(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "3.2 * 'hello';");
	}

}