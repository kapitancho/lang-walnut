<?php

namespace Walnut\Lang\Test\NativeCode\Real;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class UnaryMinusTest extends CodeExecutionTestHelper {

	public function testUnaryMinusPositive(): void {
		$result = $this->executeCodeSnippet("- {3.14};");
		$this->assertEquals("-3.14", $result);
	}

	public function testUnaryMinusNegative(): void {
		$result = $this->executeCodeSnippet("- {-4.5};");
		$this->assertEquals("4.5", $result);
	}

	public function testUnaryMinusType(): void {
		$result = $this->executeCodeSnippet(
			"myMinus(-2.5);",
			valueDeclarations: "myMinus = ^num: Real<(-3..2], [4..5.29)> => Real<(-5.29..-4], [-2..3)> :: -num;"
		);
		$this->assertEquals("2.5", $result);
	}

	public function testUnaryMinusTypeInfinity(): void {
		$result = $this->executeCodeSnippet(
			"myMinus(-2.5);",
			valueDeclarations: "myMinus = ^num: Real<(..-2], [4.7..)> => Real<(..-4.7], [2..)> :: -num;"
		);
		$this->assertEquals("2.5", $result);
	}
}