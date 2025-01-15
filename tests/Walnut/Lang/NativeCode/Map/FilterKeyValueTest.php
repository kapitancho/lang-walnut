<?php

namespace Walnut\Lang\NativeCode\Map;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class FilterKeyValueTest extends CodeExecutionTestHelper {

	public function testFilterKeyValueEmpty(): void {
		$result = $this->executeCodeSnippet("[:]->filterKeyValue(^Any => Boolean :: true);");
		$this->assertEquals("[:]", $result);
	}

	public function testFilterKeyValueNonEmpty(): void {
		$result = $this->executeCodeSnippet("[a: 1, b: 2, c: 5, d: 10, e: 5]->filterKeyValue(^[key: String, value: Integer] => Boolean :: {#key == 'a'} || {#value > 6});");
		$this->assertEquals("[a: 1, d: 10]", $result);
	}

	/* not yet supported
	public function testFilterKeyValueNonEmptyError(): void {
		$result = $this->executeCodeSnippet("[1, 2, 5, 10, 5]->filterKeyValue(^Integer => Result<Boolean, String> :: @'error');");
		$this->assertEquals("@'error'", $result);
	}*/

	public function testFilterKeyValueInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "[a: 1, b: 'a']->filterKeyValue(5);");
	}

	public function testFilterKeyValueInvalidParameterParameterType(): void {
		$this->executeErrorCodeSnippet("Invalid parameter type",
			"[a: 1, b: 'a']->filterKeyValue(^[key: String, value: Boolean] => Boolean :: true);");
	}

	public function testFilterKeyValueInvalidParameterReturnType(): void {
		$this->executeErrorCodeSnippet("Invalid parameter type",
			"[a: 1, b: 'a']->filterKeyValue(^Any => Real :: 3.14);");
	}

}