<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Integer;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class UnaryMinusTest extends CodeExecutionTestHelper {

	public function testUnaryMinusPositive(): void {
		$result = $this->executeCodeSnippet("- {3};");
		$this->assertEquals("-3", $result);
	}

	public function testUnaryMinusNegative(): void {
		$result = $this->executeCodeSnippet("- {-4};");
		$this->assertEquals("4", $result);
	}

	public function testUnaryMinusType(): void {
		$result = $this->executeCodeSnippet(
			"myMinus(-2);",
			valueDeclarations: "myMinus = ^num: Integer<(-3..2], [4..5)> => Integer<(-5..-4], [-2..3)> :: -num;"
		);
		$this->assertEquals("2", $result);
	}

	public function testUnaryMinusTypeInfinity(): void {
		$result = $this->executeCodeSnippet(
			"myMinus(-2);",
			valueDeclarations: "myMinus = ^num: Integer<(..-2], [4..)> => Integer<(..-4], [2..)> :: -num;"
		);
		$this->assertEquals("2", $result);
	}

	public function testUnaryMinusRange(): void {
		$result = $this->executeCodeSnippet("- {#->length};");
		$this->assertEquals("0", $result);
	}
}