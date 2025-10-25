<?php

namespace Walnut\Lang\Test\NativeCode\Map;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class MapTest extends CodeExecutionTestHelper {

	public function testMapEmpty(): void {
		$result = $this->executeCodeSnippet("[:]->map(^Integer => Integer :: # + 3);");
		$this->assertEquals("[:]", $result);
	}

	public function testMapNonEmpty(): void {
		$result = $this->executeCodeSnippet("[a: 1, b: 2, c: 5, d: 10, e: 5]->map(^Integer => Integer :: # + 3);");
		$this->assertEquals("[a: 4, b: 5, c: 8, d: 13, e: 8]", $result);
	}

	public function testMapNonEmptyError(): void {
		$result = $this->executeCodeSnippet("[a: 1, b: 2, c: 5, d: 10, e: 5]->map(^Integer => Result<Integer, String> :: @'error');");
		$this->assertEquals("@'error'", $result);
	}

	public function testMapInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "[a: 1, b: 'a']->map(5);");
	}

	public function testMapInvalidParameterParameterType(): void {
		$this->executeErrorCodeSnippet("The parameter type (Integer[1]|String['a']) of the callback function is not a subtype of Boolean",
			"[a: 1, b: 'a']->map(^Boolean => Boolean :: true);");
	}

}