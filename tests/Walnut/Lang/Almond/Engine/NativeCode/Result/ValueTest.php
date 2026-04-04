<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Result;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class ValueTest extends CodeExecutionTestHelper {

	public function testValueOk(): void {
		$result = $this->executeCodeSnippet(
			"doValue(Value('hello'));",
			valueDeclarations: "
				doValue = ^v: Result<Value<String>, Integer> => Result<String, Integer> :: v->value;
			"
		);
		$this->assertEquals("'hello'", $result);
	}

	public function testValueError(): void {
		$result = $this->executeCodeSnippet(
			"doValue(@42);",
			valueDeclarations: "
				doValue = ^v: Result<Value<String>, Integer> => Result<String, Integer> :: v->value;
			"
		);
		$this->assertEquals("@42", $result);
	}

	public function testValueAny(): void {
		$result = $this->executeCodeSnippet(
			"doValue(Value('hello'));",
			valueDeclarations: "
				doValue = ^v: Result<Value<String>> => Result<String> :: v->value;
			"
		);
		$this->assertEquals("'hello'", $result);
	}

	public function testValueWithFallback(): void {
		$result = $this->executeCodeSnippet(
			"doValue(@42) ?? -3.14;",
			valueDeclarations: "
				doValue = ^v: Result<Value<String>, Integer> => Result<String, Integer> :: v->value;
			"
		);
		$this->assertEquals("-3.14", $result);
	}

	public function testValueOkFallback(): void {
		$result = $this->executeCodeSnippet(
			"doValue(Value('hello')) ?? 'fallback';",
			valueDeclarations: "
				doValue = ^v: Result<Value<String>, Integer> => Result<String, Integer> :: v->value;
			"
		);
		$this->assertEquals("'hello'", $result);
	}

}
