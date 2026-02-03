<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Real;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class AbsTest extends CodeExecutionTestHelper {

	public function testAbsPositive(): void {
		$result = $this->executeCodeSnippet("3.14->abs;");
		$this->assertEquals("3.14", $result);
	}

	public function testAbsNegative(): void {
		$result = $this->executeCodeSnippet("-4.14->abs;");
		$this->assertEquals("4.14", $result);
	}

	public function testAbsMinusInfinity(): void {
		$result = $this->executeCodeSnippet(
			"abs(-4.14)",
			valueDeclarations: "abs = ^num: Real<..-3> => Real<3..> :: num->abs;",
		);
		$this->assertEquals("4.14", $result);
	}

	public function testAbsFinite(): void {
		$result = $this->executeCodeSnippet(
			"abs(-4.2)",
			valueDeclarations: "abs = ^num: Real<-5..3.1> => Real<0..5> :: num->abs;",
		);
		$this->assertEquals("4.2", $result);
	}

}