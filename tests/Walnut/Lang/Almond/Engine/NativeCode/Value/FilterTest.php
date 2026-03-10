<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Value;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class FilterTest extends CodeExecutionTestHelper {

	public function testFilterValuePasses(): void {
		$result = $this->executeCodeSnippet(
			"doValue(Value(42));",
			valueDeclarations: "
				doValue = ^v: Value<Integer> => Result<Value<Integer>, Null> ::
					v->filter(^x: Integer => Boolean :: x > 10);
			"
		);
		$this->assertEquals("Value(42)", $result);
	}

	public function testFilterValueFiltersOut(): void {
		$result = $this->executeCodeSnippet(
			"doValue(Value(42));",
			valueDeclarations: "
				doValue = ^v: Value<Integer> => Result<Value<Integer>, Null> ::
					v->filter(^x: Integer => Boolean :: x > 100);
			"
		);
		$this->assertEquals("@null", $result);
	}

	public function testFilterWithFallback(): void {
		$result = $this->executeCodeSnippet(
			"doValue(Value(42)) ?? 0;",
			valueDeclarations: "
				doValue = ^v: Value<Integer> => Result<Value<Integer>, Null> ::
					v->filter(^x: Integer => Boolean :: x > 100);
			"
		);
		$this->assertEquals("0", $result);
	}

	public function testFilterEitherValuePasses(): void {
		$result = $this->executeCodeSnippet(
			"doValue(Value('hello world!'));",
			valueDeclarations: "
				doValue = ^v: Either<String, Integer> => Result<Either<String, Integer>, Null> ::
					v->filter(^s: String => Boolean :: s->length > 5);
			"
		);
		$this->assertEquals("Value('hello world!')", $result);
	}

	public function testFilterEitherValueFiltersOut(): void {
		$result = $this->executeCodeSnippet(
			"doValue(Value('hi'));",
			valueDeclarations: "
				doValue = ^v: Either<String, Integer> => Result<Either<String, Integer>, Null> ::
					v->filter(^s: String => Boolean :: s->length > 5);
			"
		);
		$this->assertEquals("@null", $result);
	}

	// Error cases

	public function testFilterInvalidParameterType(): void {
		$this->executeErrorCodeSnippet(
			"Invalid parameter type",
			"doValue(Value(42));",
			valueDeclarations: "
				doValue = ^v: Value<Integer> => Any ::
					v->filter(5);
			"
		);
	}

	public function testFilterIncompatibleCallbackParameterType(): void {
		$this->executeErrorCodeSnippet(
			"The value type Integer is not a subtype of the callback function parameter type Boolean",
			"doValue(Value(42));",
			valueDeclarations: "
				doValue = ^v: Value<Integer> => Any ::
					v->filter(^b: Boolean => Boolean :: b);
			"
		);
	}

	public function testFilterCallbackMustReturnBoolean(): void {
		$this->executeErrorCodeSnippet(
			"The filter function must return a Boolean, but returns Integer",
			"doValue(Value(42));",
			valueDeclarations: "
				doValue = ^v: Value<Integer> => Any ::
					v->filter(^x: Integer => Integer :: x + 1);
			"
		);
	}

}
