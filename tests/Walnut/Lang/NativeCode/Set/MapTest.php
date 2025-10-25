<?php

namespace Walnut\Lang\Test\NativeCode\Set;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class MapTest extends CodeExecutionTestHelper {

	public function testSetEmpty(): void {
		$result = $this->executeCodeSnippet("[;]->map(^Integer => Integer :: # + 3);");
		$this->assertEquals("[;]", $result);
	}

	public function testSetNonEmpty(): void {
		$result = $this->executeCodeSnippet("[1; 2; 5; 10; 5]->map(^Integer => Integer :: # + 3);");
		$this->assertEquals("[4; 5; 8; 13]", $result);
	}

	public function testSetNonUnique(): void {
		$result = $this->executeCodeSnippet("['hello'; 'world'; 'hi']->map(^s: String => Integer :: s->length);");
		$this->assertEquals("[5; 2]", $result);
	}

	public function testSetNonEmptyError(): void {
		$result = $this->executeCodeSnippet("[1; 2; 5; 10; 5]->map(^Integer => Result<Integer, String> :: @'error');");
		$this->assertEquals("@'error'", $result);
	}

	public function testSetInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "[1; 'a']->map(5);");
	}

	public function testSetInvalidParameterParameterType(): void {
		$this->executeErrorCodeSnippet("The parameter type (Integer[1]|String['a']) of the callback function is not a subtype of Boolean",
			"[1; 'a']->map(^Boolean => Boolean :: true);");
	}

}