<?php

namespace Walnut\Lang\Test\NativeCode\Real;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class BinaryPlusTest extends CodeExecutionTestHelper {

	public function testBinaryPlus(): void {
		$result = $this->executeCodeSnippet("3.14 + 5;");
		$this->assertEquals("8.14", $result);
	}

	public function testBinaryPlusReal(): void {
		$result = $this->executeCodeSnippet("3.14 + 5.14;");
		$this->assertEquals("8.28", $result);
	}

	public function testBinaryMinusSubsets(): void {
		$result = $this->executeCodeSnippet(
			"plus[5.4, 3];",
			valueDeclarations: "plus = ^[a: Real[5.4, 2], b: Integer[2, 3]] => Real[4, 5, 7.4, 8.4] :: #a + #b;"
		);
		$this->assertEquals("8.4", $result);
	}

	public function testBinaryPlusStandard(): void {
		$result = $this->executeCodeSnippet(
			"plus(0);",
			valueDeclarations: "v = 3.14; plus = ^p: Real<0..5> => Real<3.14..8.14> :: p + v;"
		);
		$this->assertEquals("3.14", $result);
	}

	public function testBinaryPlusZeroParameter(): void {
		$result = $this->executeCodeSnippet(
			"plus(0);",
			valueDeclarations: "v = 3.14; plus = ^p: Real<0> => Real<3.14> :: p + v;"
		);
		$this->assertEquals("3.14", $result);
	}

	public function testBinaryPlusZeroTarget(): void {
		$result = $this->executeCodeSnippet(
			"plus(0);",
			valueDeclarations: "v = 3.14; plus = ^p: Real<0> => Real<3.14> :: v + p;"
		);
		$this->assertEquals("3.14", $result);
	}

	public function testBinaryPlusInvalidParameter(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "3.14 + 'hello';");
	}

}