<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Real;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

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

	public function testRoundAsDecimalInvalidParameterTypeInteger(): void {
		$this->executeErrorCodeSnippet(
			"The parameter type should be a subtype of Integer<0..>, got Integer[-2].",
			"-3.77->roundAsDecimal(-2);"
		);
	}

	public function testRoundAsDecimalInvalidParameterType(): void {
		$this->executeErrorCodeSnippet(
			"Invalid parameter type: String['hello']",
			"-3.77->roundAsDecimal('hello');"
		);
	}

}