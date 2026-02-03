<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Set;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class FilterTest extends CodeExecutionTestHelper {

	public function testFilterEmpty(): void {
		$result = $this->executeCodeSnippet("[;]->filter(^Any => Boolean :: true);");
		$this->assertEquals("[;]", $result);
	}

	public function testFilterNonEmpty(): void {
		$result = $this->executeCodeSnippet("[1; 2; 5; 10; 5]->filter(^Integer => Boolean :: # > 4);");
		$this->assertEquals("[5; 10]", $result);
	}

	public function testFilterNonEmptyError(): void {
		$result = $this->executeCodeSnippet("[1; 2; 5; 10; 5]->filter(^Integer => Result<Boolean, String> :: @'error');");
		$this->assertEquals("@'error'", $result);
	}

	public function testFilterInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "[1; 'a']->filter(5);");
	}

	public function testFilterInvalidParameterParameterType(): void {
		$this->executeErrorCodeSnippet("The parameter type (Integer[1]|String['a']) of the callback function is not a subtype of Boolean",
			"[1; 'a']->filter(^Boolean => Boolean :: true);");
	}

	public function testFilterInvalidParameterReturnType(): void {
		$this->executeErrorCodeSnippet("Invalid parameter type",
			"[1; 'a']->filter(^Any => Real :: 3.14);");
	}

}