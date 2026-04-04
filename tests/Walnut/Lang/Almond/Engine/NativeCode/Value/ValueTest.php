<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Value;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class ValueTest extends CodeExecutionTestHelper {

	public function testValueExtractsInner(): void {
		$result = $this->executeCodeSnippet(
			"doValue(Value('hello'));",
			valueDeclarations: "
				doValue = ^v: Value<String> => String :: v->value;
			"
		);
		$this->assertEquals("'hello'", $result);
	}

	public function testValueTypeOfValue(): void {
		$result = $this->executeCodeSnippet(
			"doValue(Value('hello'));",
			valueDeclarations: "
				doValue = ^v: Result<Value<String>, Integer> => String :: ?whenTypeOf(v) {
				    `Value<String>: v->value,
					`Error<Integer>: 'error'
				};
			"
		);
		$this->assertEquals("'hello'", $result);
	}

	public function testValueTypeOfError(): void {
		$result = $this->executeCodeSnippet(
			"doValue(@42);",
			valueDeclarations: "
				doValue = ^v: Result<Value<String>, Integer> => String :: ?whenTypeOf(v) {
				    `Value<String>: v->value,
					`Error<Integer>: 'error'
				};
			"
		);
		$this->assertEquals("'error'", $result);
	}

	public function testValueExtractsInteger(): void {
		$result = $this->executeCodeSnippet(
			"doValue(Value(42));",
			valueDeclarations: "
				doValue = ^v: Value<Integer> => Integer :: v->value;
			"
		);
		$this->assertEquals("42", $result);
	}

}
