<?php

namespace Walnut\Lang\NativeCode\Integer;

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
}