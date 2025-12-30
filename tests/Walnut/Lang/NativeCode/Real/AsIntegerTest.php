<?php

namespace Walnut\Lang\Test\NativeCode\Real;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class AsIntegerTest extends CodeExecutionTestHelper {

	public function testAsIntegerPositive(): void {
		$result = $this->executeCodeSnippet("3.14->asInteger;");
		$this->assertEquals("3", $result);
	}

	public function testAsIntegerPositiveRounding(): void {
		$result = $this->executeCodeSnippet("3.77->asInteger;");
		$this->assertEquals("3", $result);
	}

	public function testAsIntegerNegative(): void {
		$result = $this->executeCodeSnippet("-3.14->asInteger;");
		$this->assertEquals("-3", $result);
	}

	public function testAsIntegerNegativeRounding(): void {
		$result = $this->executeCodeSnippet("-3.77->asInteger;");
		$this->assertEquals("-3", $result);
	}

	public function testAsIntegerReturnTypeReal(): void {
		$result = $this->executeCodeSnippet(
			"asInteger(-2.77);",
			valueDeclarations: "
				asInteger = ^r: Real<[-3.5..3.5]> => Integer<[-3..3]> :: r->asInteger;
			"
		);
		$this->assertEquals("-2", $result);
	}

	public function testAsIntegerReturnTypeIntegerClosed(): void {
		$result = $this->executeCodeSnippet(
			"asInteger(-2.77);",
			valueDeclarations: "
				asInteger = ^r: Real<[-3..3]> => Integer<[-3..3]> :: r->asInteger;
			"
		);
		$this->assertEquals("-2", $result);
	}

	public function testAsIntegerReturnTypeIntegerOpen(): void {
		$result = $this->executeCodeSnippet(
			"asInteger(-2.77);",
			valueDeclarations: "
				asInteger = ^r: Real<(-3..3)> => Integer<[-3..3]> :: r->asInteger;
			"
		);
		$this->assertEquals("-2", $result);
	}
}