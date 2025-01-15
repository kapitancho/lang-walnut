<?php

namespace Walnut\Lang\NativeCode\Set;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class MapTest extends CodeExecutionTestHelper {

	public function testMapEmpty(): void {
		$result = $this->executeCodeSnippet("[;]->map(^Integer => Integer :: # + 3);");
		$this->assertEquals("[;]", $result);
	}

	public function testMapNonEmpty(): void {
		$result = $this->executeCodeSnippet("[1; 2; 5; 10; 5]->map(^Integer => Integer :: # + 3);");
		$this->assertEquals("[4; 5; 8; 13]", $result);
	}

	public function testMapNonEmptyError(): void {
		$result = $this->executeCodeSnippet("[1; 2; 5; 10; 5]->map(^Integer => Result<Integer, String> :: @'error');");
		$this->assertEquals("@'error'", $result);
	}

	public function testMapInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "[1; 'a']->map(5);");
	}

	public function testMapInvalidParameterParameterType(): void {
		$this->executeErrorCodeSnippet("The parameter type (Integer[1]|String['a']) of the callback function is not a subtype of Boolean",
			"[1; 'a']->map(^Boolean => Boolean :: true);");
	}

}