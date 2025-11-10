<?php

namespace Walnut\Lang\Test\NativeCode\Real;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class BinaryMinusTest extends CodeExecutionTestHelper {

	public function testBinaryMinus(): void {
		$result = $this->executeCodeSnippet("3.5 - 5;");
		$this->assertEquals("-1.5", $result);
	}

	public function testBinaryMinusReal(): void {
		$result = $this->executeCodeSnippet("3.14 - 5.14;");
		$this->assertEquals("-2", $result);
	}

	public function testBinaryMinusZeroParameter(): void {
		$result = $this->executeCodeSnippet(
			"minus(0);",
			valueDeclarations: "v = 3.14; minus = ^p: Real<0> => Real<-3.14> :: p - v;"
		);
		$this->assertEquals("-3.14", $result);
	}

	public function testBinaryMinusZeroTarget(): void {
		$result = $this->executeCodeSnippet(
			"minus(0);",
			valueDeclarations: "v = 3.14; minus = ^p: Real<0> => Real<3.14> :: v - p;"
		);
		$this->assertEquals("3.14", $result);
	}

	public function testBinaryPlusInfinityParameter(): void {
		$result = $this->executeCodeSnippet("minus(-20);", valueDeclarations: <<<NUT
			minus = ^x: Integer<..3> => Real<2.4..> :: 5.4 - x;
		NUT);
		$this->assertEquals("25.4", $result);
	}

	public function testBinaryMinusInfinityParameter(): void {
		$result = $this->executeCodeSnippet("minus(20);", valueDeclarations: <<<NUT
			minus = ^x: Integer<3..> => Real<..2.4> :: 5.4 - x;
		NUT);
		$this->assertEquals("-14.6", $result);
	}

	public function testBinaryMinusInvalidParameter(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "3.14 - 'hello';");
	}

}