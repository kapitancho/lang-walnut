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

	public function testMapKeyValueNonEmptyEmptyValue(): void {
		$result = $this->executeCodeSnippet("[a: 1, b: 2, c: 5, d: 10, eee: 5]->mapKeyValue(^[key: String, value: Integer] => Optional<Integer> :: {
			x = {#key->length} + #value;
			?whenTypeOf(x) { `Integer<10..>: empty, ~: x }
		});");
		$this->assertEquals("[a: 2, b: 3, c: 6, eee: 8]", $result);
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

	public function testMapKeyValueReturnTypeNoError(): void {
		$result = $this->executeCodeSnippet("fn[a: 1, b: 2, c: 5]",
			valueDeclarations: "
				fn = ^m: Map<String<1..3>:Integer, 2..5> => Map<String<1..3>:Integer, 2..5> ::
					m->mapKeyValue(^[key: String<1..3>, value: Integer] => Integer :: #value + #key->length);
			"
		);
		$this->assertEquals("[a: 2, b: 3, c: 6]", $result);
	}

	public function testMapKeyValueReturnTypeNoErrorOptional(): void {
		$result = $this->executeCodeSnippet("fn[a: 1, b: 2, c: 5]",
			valueDeclarations: "
				fn = ^m: Map<String<1..3>:Integer, 2..5> => Map<String<1..3>:Integer, ..5> ::
					m->mapKeyValue(^[key: String<1..3>, value: Integer] => Optional<Integer> :: {
						?whenTypeOf(#value) { `Integer<5..>: empty, ~: #value + #key->length }
					});
			"
		);
		$this->assertEquals("[a: 2, b: 3]", $result);
	}

	public function testMapKeyValueReturnTypeResultNoError(): void {
		$result = $this->executeCodeSnippet("fn[a: 1, b: 2, c: 5]",
			valueDeclarations: "
				fn = ^m: Map<String<1..3>:Integer, 2..5> => Result<Map<String<1..3>:Integer, 2..5>, String> ::
					m->mapKeyValue(^[key: String<1..3>, value: Integer] => Result<Integer, String> :: #value + #key->length);
			"
		);
		$this->assertEquals("[a: 2, b: 3, c: 6]", $result);
	}

	public function testMapKeyValueReturnTypeResultNoErrorEmpty(): void {
		$result = $this->executeCodeSnippet("fn[a: 1, b: 2, c: 5]",
			valueDeclarations: "
				fn = ^m: Map<String<1..3>:Integer, 2..5> => Result<Map<String<1..3>:Integer, ..5>, String> ::
					m->mapKeyValue(^[key: String<1..3>, value: Integer] => Result<Optional<Integer>, String> :: {
						?whenTypeOf(#value) { `Integer<5..>: empty, ~: #value + #key->length }
					});
			"
		);
		$this->assertEquals("[a: 2, b: 3]", $result);
	}

	public function testMapKeyValueReturnTypeResultError(): void {
		$result = $this->executeCodeSnippet("fn[a: 1, b: 2, c: 5]",
			valueDeclarations: "
				fn = ^m: Map<String<1..3>:Integer, 2..5> => Result<Map<String<1..3>:Integer, 2..5>, String> ::
					m->mapKeyValue(^[key: String<1..3>, value: Integer] => Result<Integer, String> :: @'error');
			"
		);
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