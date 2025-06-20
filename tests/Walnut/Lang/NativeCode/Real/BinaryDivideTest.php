<?php

namespace Walnut\Lang\NativeCode\Real;

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

	public function testBinaryDivideInvalidParameter(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "3.6 / 'hello';");
	}

	public function testBinaryDivideReturnTypeOk(): void {
		$result = $this->executeCodeSnippet("div[3.6, 1.5];", <<<NUT
			div = ^[a: Real, b: Real<(..0), (0..)>] => Real :: #a / #b;
		NUT);
		$this->assertEquals("2.4", $result);
	}

}