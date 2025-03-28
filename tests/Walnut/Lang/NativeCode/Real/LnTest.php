<?php

namespace Walnut\Lang\NativeCode\Real;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class LnTest extends CodeExecutionTestHelper {

	public function testLnPositive(): void {
		$result = $this->executeCodeSnippet("3.14->ln;");
		$this->assertEquals("1.1442227999202", $result);
	}

	public function testLnNegative(): void {
		$result = $this->executeCodeSnippet("-4.14->ln;");
		$this->assertEquals("@NotANumber()", $result);
	}
}