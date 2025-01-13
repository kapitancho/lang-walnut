<?php

namespace Walnut\Lang\NativeCode\Integer;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class SquareTest extends CodeExecutionTestHelper {

	public function testSquarePositive(): void {
		$result = $this->executeCodeSnippet("3->square;");
		$this->assertEquals("9", $result);
	}

	public function testSquareNegative(): void {
		$result = $this->executeCodeSnippet("-4->square;");
		$this->assertEquals("16", $result);
	}
}