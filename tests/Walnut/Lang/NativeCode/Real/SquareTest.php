<?php

namespace Walnut\Lang\Test\NativeCode\Real;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class SquareTest extends CodeExecutionTestHelper {

	public function testSquarePositive(): void {
		$result = $this->executeCodeSnippet("3.2->square;");
		$this->assertEquals("10.24", $result);
	}

	public function testSquareNegative(): void {
		$result = $this->executeCodeSnippet("-1.5->square;");
		$this->assertEquals("2.25", $result);
	}

	public function testSquareNegativeRange(): void {
		$result = $this->executeCodeSnippet(
			"f(-11);",
			valueDeclarations: "f = ^num: Real<-20..-3.14> => Real<9..400> :: num->square;"
		);
		$this->assertEquals("121", $result);
	}
}