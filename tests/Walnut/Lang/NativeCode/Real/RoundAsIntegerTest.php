<?php

namespace Walnut\Lang\Test\NativeCode\Real;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class RoundAsIntegerTest extends CodeExecutionTestHelper {

	public function testRoundAsIntegerPositive(): void {
		$result = $this->executeCodeSnippet("3.14->roundAsInteger;");
		$this->assertEquals("3", $result);
	}

	public function testRoundAsIntegerPositiveRounding(): void {
		$result = $this->executeCodeSnippet("3.77->roundAsInteger;");
		$this->assertEquals("4", $result);
	}

	public function testRoundAsIntegerNegative(): void {
		$result = $this->executeCodeSnippet("-3.14->roundAsInteger;");
		$this->assertEquals("-3", $result);
	}

	public function testRoundAsIntegerNegativeRounding(): void {
		$result = $this->executeCodeSnippet("-3.77->roundAsInteger;");
		$this->assertEquals("-4", $result);
	}
}