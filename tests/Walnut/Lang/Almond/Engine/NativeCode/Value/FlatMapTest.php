<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Value;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class FlatMapTest extends CodeExecutionTestHelper {

	public function testFlatMapValue(): void {
		$result = $this->executeCodeSnippet(
			"doValue(Value(42));",
			valueDeclarations: "
				doValue = ^v: Value<Integer> => Value<String> ::
					v->flatMap(^x: Integer => Value<String> :: Value(x->asString));
			"
		);
		$this->assertEquals("Value('42')", $result);
	}

	public function testFlatMapValueChained(): void {
		$result = $this->executeCodeSnippet(
			"doValue(Value(42));",
			valueDeclarations: "
				doValue = ^v: Value<Integer> => Value<String> ::
					v->flatMap(^x: Integer => Value<Integer> :: Value(x + 1))
					 ->flatMap(^y: Integer => Value<String> :: Value(y->asString));
			"
		);
		$this->assertEquals("Value('43')", $result);
	}

	public function testFlatMapSubtype(): void {
		$result = $this->executeCodeSnippet(
			"r2(Value(15));",
			valueDeclarations: "
				r2 = ^g: Value<Integer<10..20>> => Value<Integer<16..26>> ::
					g->flatMap(^c: Integer<10..20> => Value<Integer<16..26>> :: Value(c + 6));
			"
		);
		$this->assertEquals("Value(21)", $result);
	}

	// Error cases

	public function testFlatMapInvalidParameterType(): void {
		$this->executeErrorCodeSnippet(
			"Invalid parameter type",
			"doValue(Value(42));",
			valueDeclarations: "
				doValue = ^v: Value<Integer> => Any ::
					v->flatMap(5);
			"
		);
	}

	public function testFlatMapIncompatibleCallbackParameterType(): void {
		$this->executeErrorCodeSnippet(
			"The value type Integer is not a subtype of the callback function parameter type Boolean",
			"doValue(Value(42));",
			valueDeclarations: "
				doValue = ^v: Value<Integer> => Any ::
					v->flatMap(^b: Boolean => Value<Boolean> :: Value(b));
			"
		);
	}

	public function testFlatMapCallbackMustReturnValue(): void {
		$this->executeErrorCodeSnippet(
			"The flatMap function must return a Value<...> type, but returns Integer",
			"doValue(Value(42));",
			valueDeclarations: "
				doValue = ^v: Value<Integer> => Any ::
					v->flatMap(^x: Integer => Integer :: x + 1);
			"
		);
	}

}
