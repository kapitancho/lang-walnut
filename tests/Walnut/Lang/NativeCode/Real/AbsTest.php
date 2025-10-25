<?php

namespace Walnut\Lang\Test\NativeCode\Real;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class AbsTest extends CodeExecutionTestHelper {

	public function testAbsPositive(): void {
		$result = $this->executeCodeSnippet("3.14->abs;");
		$this->assertEquals("3.14", $result);
	}

	public function testAbsNegative(): void {
		$result = $this->executeCodeSnippet("-4.14->abs;");
		$this->assertEquals("4.14", $result);
	}
}