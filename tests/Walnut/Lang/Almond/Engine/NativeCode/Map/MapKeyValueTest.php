<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Map;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class MapKeyValueTest extends CodeExecutionTestHelper {

	public function testMapKeyValueEmpty(): void {
		$result = $this->executeCodeSnippet("[:]->mapKeyValue(^[key: String, value: Integer] => Integer :: {#key->length} + #value);");
		$this->assertEquals("[:]", $result);
	}

	public function testMapKeyValueNonEmpty(): void {
		$result = $this->executeCodeSnippet("[a: 1, b: 2, c: 5, d: 10, eee: 5]->mapKeyValue(^[key: String, value: Integer] => Integer :: {#key->length} + #value);");
		$this->assertEquals("[a: 2, b: 3, c: 6, d: 11, eee: 8]", $result);
	}

	public function testMapKeyValueKeyType(): void {
		$result = $this->executeCodeSnippet(
			"fn[a: 1, b: 2];",
			valueDeclarations: "fn = ^m: Map<String<1>:Integer> => Map<String<1>:Boolean> :: 
				m->mapKeyValue(^[key: String<1>, value: Integer] => Boolean :: #value > #key->length);"
		);
		$this->assertEquals("[a: false, b: true]", $result);
	}

	public function testMapKeyValueNonEmptyError(): void {
		$result = $this->executeCodeSnippet("[a: 1, b: 2, c: 5, d: 10, eee: 5]->mapKeyValue(^[key: String, value: Integer] => Result<Integer, String> :: @'error');");
		$this->assertEquals("@'error'", $result);
	}

	public function testMapKeyValueInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "[a: 1, b: 'a']->mapKeyValue(5);");
	}

	public function testMapKeyValueInvalidParameterParameterType(): void {
		$this->executeErrorCodeSnippet("of the callback function is not a subtype of",
			"[a: 1, b: 2, c: 5, d: 10, eee: 5]->mapKeyValue(^[key: String] => Integer :: {#key->length} + 3);");
	}

}