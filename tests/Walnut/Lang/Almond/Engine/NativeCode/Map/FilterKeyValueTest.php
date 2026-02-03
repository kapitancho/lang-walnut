<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Map;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class FilterKeyValueTest extends CodeExecutionTestHelper {

	public function testFilterKeyValueEmpty(): void {
		$result = $this->executeCodeSnippet("[:]->filterKeyValue(^Any => Boolean :: true);");
		$this->assertEquals("[:]", $result);
	}

	public function testFilterKeyValueNonEmpty(): void {
		$result = $this->executeCodeSnippet("[a: 1, b: 2, c: 5, d: 10, e: 5]->filterKeyValue(^[key: String, value: Integer] => Boolean :: {#key == 'a'} || {#value > 6});");
		$this->assertEquals("[a: 1, d: 10]", $result);
	}

	public function testFilterKeyValueKeyType(): void {
		$result = $this->executeCodeSnippet(
			"fn[a: 1, b: 2];",
			valueDeclarations: "fn = ^m: Map<String<1>:Integer> => Map<String<1>:Integer> :: 
				m->filterKeyValue(^[key: String<1>, value: Integer] => Boolean :: #value > #key->length);"
		);
		$this->assertEquals("[b: 2]", $result);
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