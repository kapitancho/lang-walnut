<?php

namespace Walnut\Lang\Test\NativeCode\Result;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class MapKeyValueTest extends CodeExecutionTestHelper {

	public function testMapKeyValueEmptyMap(): void {
		$result = $this->executeCodeSnippet(
			"doMap([:]);",
			valueDeclarations: "
				doMap = ^m: Result<Map<Integer>, Null> => Result<Map<Integer>, Null> ::
					m->mapKeyValue(^[key: String, value: Integer] => Integer :: {#key->length} + #value);
			"
		);
		$this->assertEquals("[:]", $result);
	}

	public function testMapKeyValueNonEmpty(): void {
		$result = $this->executeCodeSnippet(
			"doMap([a: 1, b: 2, c: 5, d: 10, eee: 5]);",
			valueDeclarations: "
				doMap = ^m: Result<Map<Integer>, Null> => Result<Map<Integer>, Null> ::
					m->mapKeyValue(^[key: String, value: Integer] => Integer :: {#key->length} + #value);
			"
		);
		$this->assertEquals("[a: 2, b: 3, c: 6, d: 11, eee: 8]", $result);
	}

	public function testMapKeyValueNonEmptyCallbackError(): void {
		$result = $this->executeCodeSnippet(
			"doMap([a: 1, b: 2, c: 5, d: 10, eee: 5]);",
			valueDeclarations: "
				doMap = ^m: Result<Map<Integer>, Null> => Result<Map<Integer>, Null|String> ::
					m->mapKeyValue(^[key: String, value: Integer] => Result<Integer, String> :: @'error');
			"
		);
		$this->assertEquals("@'error'", $result);
	}

	public function testMapKeyValueWithResultError(): void {
		$result = $this->executeCodeSnippet(
			"doMap(@'initial_error');",
			valueDeclarations: "
				doMap = ^m: Result<Map<Integer>, String> => Result<Map<Integer>, String> ::
					m->mapKeyValue(^[key: String, value: Integer] => Integer :: {#key->length} + #value);
			"
		);
		$this->assertEquals("@'initial_error'", $result);
	}

	public function testMapKeyValueWithResultErrorAndCallbackError(): void {
		$result = $this->executeCodeSnippet(
			"doMap(@'first_error');",
			valueDeclarations: "
				doMap = ^m: Result<Map<Integer>, String> => Result<Map<Integer>, String|Integer> ::
					m->mapKeyValue(^[key: String, value: Integer] => Result<Integer, Integer> :: @999);
			"
		);
		$this->assertEquals("@'first_error'", $result);
	}

	public function testMapKeyValueWithNullError(): void {
		$result = $this->executeCodeSnippet(
			"doMap[a: 1, b: 2];",
			valueDeclarations: "
				doMap = ^m: Result<Map<Integer>, Null> => Result<Map<Integer>, Null> ::
					m->mapKeyValue(^[key: String, value: Integer] => Integer :: {#key->length} + #value);
			"
		);
		$this->assertEquals("[a: 2, b: 3]", $result);
	}

	public function testMapKeyValueResultErrorNull(): void {
		$result = $this->executeCodeSnippet(
			"doMap(@null);",
			valueDeclarations: "
				doMap = ^m: Result<Map<Integer>, Null> => Result<Map<Integer>, Null> ::
					m->mapKeyValue(^[key: String, value: Integer] => Integer :: {#key->length} + #value);
			"
		);
		$this->assertEquals("@null", $result);
	}

	public function testMapKeyValueWithNullErrorKeyType(): void {
		$result = $this->executeCodeSnippet(
			"doMap[a: 1, b: 2];",
			valueDeclarations: "
				doMap = ^m: Result<Map<String<1>:Integer>, Null> => Result<Map<String<1>:Integer>, Null> ::
					m->mapKeyValue(^[key: String<1>, value: Integer] => Integer :: {#key->length} + #value);
			"
		);
		$this->assertEquals("[a: 2, b: 3]", $result);
	}

	public function testMapKeyValueInvalidReturnType(): void {
		$this->executeErrorCodeSnippet(
			"Expected a return value of type Map<Integer>, got Result<Map<Integer>, Null>",
			"doMap([:]);",
			valueDeclarations: "
				doMap = ^m: Result<Map<Integer>, Null> => Map<Integer> ::
					m->mapKeyValue(^[key: String, value: Integer] => Integer :: {#key->length} + #value);
			"
		);
	}

	public function testMapKeyValueInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type',
			"doMap([:]);",
			valueDeclarations: "
				doMap = ^m: Result<Map<Integer>, Null> => Result<Map<Integer>, Null> ::
					m->mapKeyValue(5);
			"
		);
	}

	public function testMapKeyValueInvalidParameterParameterType(): void {
		$this->executeErrorCodeSnippet("of the callback function is not a subtype of",
			"doMap[a: 1, b: 2, c: 5, d: 10, eee: 5];",
			valueDeclarations: "
				doMap = ^m: Result<Map<Integer>, Null> => Result<Map<Integer>, Null> ::
					m->mapKeyValue(^[key: String] => Integer :: {#key->length} + 3);
			"
		);
	}

}
