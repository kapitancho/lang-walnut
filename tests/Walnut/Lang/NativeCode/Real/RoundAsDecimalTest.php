<?php

namespace Walnut\Lang\NativeCode\Real;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class RoundAsDecimalTest extends CodeExecutionTestHelper {

	public function testRoundAsDecimalPositive(): void {
		$result = $this->executeCodeSnippet("3.14->roundAsDecimal(1);");
		$this->assertEquals("3.1", $result);
	}

	public function testRoundAsDecimalPositiveRounding(): void {
		$result = $this->executeCodeSnippet("3.77->roundAsDecimal(1);");
		$this->assertEquals("3.8", $result);
	}

	public function testRoundAsDecimalNegative(): void {
		$result = $this->executeCodeSnippet("-3.14->roundAsDecimal(1);");
		$this->assertEquals("-3.1", $result);
	}

	public function testRoundAsDecimalNegativeRounding(): void {
		$result = $this->executeCodeSnippet("-3.77->roundAsDecimal(1);");
		$this->assertEquals("-3.8", $result);
	}
}