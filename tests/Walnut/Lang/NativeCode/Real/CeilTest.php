<?php

namespace Walnut\Lang\Test\NativeCode\Real;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class CeilTest extends CodeExecutionTestHelper {

	public function testCeilPositive(): void {
		$result = $this->executeCodeSnippet("3.14->ceil;");
		$this->assertEquals("4", $result);
	}

	public function testCeilPositiveRounding(): void {
		$result = $this->executeCodeSnippet("3.77->ceil;");
		$this->assertEquals("4", $result);
	}

	public function testCeilNegative(): void {
		$result = $this->executeCodeSnippet("-3.14->ceil;");
		$this->assertEquals("-3", $result);
	}

	public function testCeilNegativeRounding(): void {
		$result = $this->executeCodeSnippet("-3.77->ceil;");
		$this->assertEquals("-3", $result);
	}
}