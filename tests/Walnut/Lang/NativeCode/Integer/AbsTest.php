<?php

namespace Walnut\Lang\Test\NativeCode\Integer;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class AbsTest extends CodeExecutionTestHelper {

	public function testAbsPositive(): void {
		$result = $this->executeCodeSnippet("3->abs;");
		$this->assertEquals("3", $result);
	}

	public function testAbsNegative(): void {
		$result = $this->executeCodeSnippet("-4->abs;");
		$this->assertEquals("4", $result);
	}

	public function testAbsMinusInfinity(): void {
		$result = $this->executeCodeSnippet(
			"abs(-4)",
			valueDeclarations: "abs = ^num: Integer<..-3> => Integer<3..> :: num->abs;",
		);
		$this->assertEquals("4", $result);
	}

	public function testAbsFinite(): void {
		$result = $this->executeCodeSnippet(
			"abs(-4)",
			valueDeclarations: "abs = ^num: Integer<-5..3> => Integer<0..5> :: num->abs;",
		);
		$this->assertEquals("4", $result);
	}
}