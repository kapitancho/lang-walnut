<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\String;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class UnaryMinusTest extends CodeExecutionTestHelper {

	public function testUnaryMinusSingle(): void {
		$result = $this->executeCodeSnippet("- {'hello'};");
		$this->assertEquals("'olleh'", $result);
	}

	public function testUnaryMinusDouble(): void {
		$result = $this->executeCodeSnippet("- {- 'hello'};");
		$this->assertEquals("'hello'", $result);
	}

	public function testUnaryMinusTypeRange(): void {
		$result = $this->executeCodeSnippet(
			"myMinus('hello');",
			valueDeclarations: "myMinus = ^str: String<3..10> => String<3..10> :: -str;"
		);
		$this->assertEquals("'olleh'", $result);
	}

	public function testUnaryMinusTypeSubset(): void {
		$result = $this->executeCodeSnippet(
			"myMinus('hello');",
			valueDeclarations: "myMinus = ^str: String['abc', 'hello', 'abba'] => String['cba', 'olleh', 'abba'] :: -str;"
		);
		$this->assertEquals("'olleh'", $result);
	}
}