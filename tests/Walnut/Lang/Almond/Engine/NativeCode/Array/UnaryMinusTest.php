<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Array;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class UnaryMinusTest extends CodeExecutionTestHelper {

	public function testUnaryMinusEmpty(): void {
		$result = $this->executeCodeSnippet("-[];");
		$this->assertEquals("[]", $result);
	}

	public function testUnaryMinusNonEmpty(): void {
		$result = $this->executeCodeSnippet("-[1, 2];");
		$this->assertEquals("[2, 1]", $result);
	}

	public function testUnaryMinusTypeRange(): void {
		$result = $this->executeCodeSnippet(
			"myMinus['hello', 'bye'];",
			valueDeclarations: "myMinus = ^arr: Array<String<1..5>, 2..10> => Array<String<1..5>, 2..10> :: -arr;"
		);
		$this->assertEquals("['bye', 'hello']", $result);
	}

	public function testUnaryMinusTypeTuple(): void {
		$result = $this->executeCodeSnippet(
			"myMinus['hello', 3.14, -42];",
			valueDeclarations: "myMinus = ^arr: [String<5>, Real<(0..)>, Real] => [Real, Real<(0..)>, String<5>] :: -arr;"
		);
		$this->assertEquals("[-42, 3.14, 'hello']", $result);
	}

	public function testUnaryMinusTypeWithRest(): void {
		$result = $this->executeCodeSnippet(
			"myMinus['hello', 3.14, -42];",
			valueDeclarations: "myMinus = ^arr: [String<5>, Real<(0..)>, ...Real] => Array<String<5>|Real, 2..> :: -arr;"
		);
		$this->assertEquals("[-42, 3.14, 'hello']", $result);
	}

}