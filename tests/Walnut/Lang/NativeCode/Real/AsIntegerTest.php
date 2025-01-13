<?php

namespace Walnut\Lang\NativeCode\Real;

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
}