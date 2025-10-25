<?php

namespace Walnut\Lang\Test\NativeCode\Real;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class LnTest extends CodeExecutionTestHelper {

	public function testLnPositive(): void {
		$result = $this->executeCodeSnippet("3.14->ln;");
		$this->assertEquals("1.1442227999202", $result);
	}

	public function testLnNegative(): void {
		$result = $this->executeCodeSnippet("-4.14->ln;");
		$this->assertEquals("@NotANumber", $result);
	}

	public function testLnZero(): void {
		$result = $this->executeCodeSnippet("0->ln;");
		$this->assertEquals("@NotANumber", $result);
	}

	public function testLnZeroInTypeOpen(): void {
		$result = $this->executeCodeSnippet("l(1);", valueDeclarations: <<<NUT
			l = ^v: Real<(0..)> => Real :: v->ln;
		NUT);
		$this->assertEquals("0", $result);
	}

	public function testLnZeroInTypeClosed(): void {
		$this->executeErrorCodeSnippet(
			"expected a return value of type Real, got Result<Real, NotANumber>",
			"l(1);", valueDeclarations:  <<<NUT
			l = ^v: Real<[0..)> => Real :: v->ln;
		NUT);
	}
}