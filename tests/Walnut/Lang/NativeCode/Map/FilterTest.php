<?php

namespace Walnut\Lang\Test\NativeCode\Map;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class FilterTest extends CodeExecutionTestHelper {

	public function testFilterEmpty(): void {
		$result = $this->executeCodeSnippet("[:]->filter(^Any => Boolean :: true);");
		$this->assertEquals("[:]", $result);
	}

	public function testFilterNonEmpty(): void {
		$result = $this->executeCodeSnippet("[a: 1, b: 2, c: 5, d: 10, e: 5]->filter(^Integer => Boolean :: # > 4);");
		$this->assertEquals("[c: 5, d: 10, e: 5]", $result);
	}

	public function testFilterKeyType(): void {
		$result = $this->executeCodeSnippet(
			"fn[a: 1, b: 2];",
			valueDeclarations: "fn = ^m: Map<String<1>:Integer> => Map<String<1>:Integer> :: 
				m->filter(^v: Integer => Boolean :: v > 1);"
		);
		$this->assertEquals("[b: 2]", $result);
	}

	/* not yet supported
	public function testFilterNonEmptyError(): void {
		$result = $this->executeCodeSnippet("[1, 2, 5, 10, 5]->filter(^Integer => Result<Boolean, String> :: @'error');");
		$this->assertEquals("@'error'", $result);
	}*/

	public function testFilterInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "[a: 1, b: 'a']->filter(5);");
	}

	public function testFilterInvalidParameterParameterType(): void {
		$this->executeErrorCodeSnippet("The parameter type (Integer[1]|String['a']) of the callback function is not a subtype of Boolean",
			"[a: 1, b: 'a']->filter(^Boolean => Boolean :: true);");
	}

	public function testFilterInvalidParameterReturnType(): void {
		$this->executeErrorCodeSnippet("Invalid parameter type",
			"[a: 1, b: 'a']->filter(^Any => Real :: 3.14);");
	}

}