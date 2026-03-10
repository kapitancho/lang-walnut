<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Value;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class MapTest extends CodeExecutionTestHelper {

	public function testMapValue(): void {
		$result = $this->executeCodeSnippet(
			"doValue(Value(42));",
			valueDeclarations: "
				doValue = ^v: Value<Integer> => Value<Integer> ::
					v->map(^x: Integer => Integer :: x + 1);
			"
		);
		$this->assertEquals("Value(43)", $result);
	}

	public function testMapValueChained(): void {
		$result = $this->executeCodeSnippet(
			"doValue(Value(42));",
			valueDeclarations: "
				doValue = ^v: Value<Integer> => Value<String> ::
					v->map(^x: Integer => Integer :: x + 1)
					 ->map(^y: Integer => String :: y->asString);
			"
		);
		$this->assertEquals("Value('43')", $result);
	}

	public function testMapValueWithResultReturnType(): void {
		$result = $this->executeCodeSnippet(
			"doValue(Value(42));",
			valueDeclarations: "
				doValue = ^v: Value<Integer> => Result<Value<String>, Null> ::
					v->map(^x: Integer => Result<String, Null> ::
						?whenValueOf(x) { 0: @null, ~: x->asString });
			"
		);
		$this->assertEquals("Value('42')", $result);
	}

	public function testMapValueWithResultReturnTypeError(): void {
		$result = $this->executeCodeSnippet(
			"doValue(Value(0));",
			valueDeclarations: "
				doValue = ^v: Value<Integer> => Result<Value<String>, Null> ::
					v->map(^x: Integer => Result<String, Null> ::
						?whenValueOf(x) { 0: @null, ~: x->asString });
			"
		);
		$this->assertEquals("@null", $result);
	}

	public function testMapEitherValue(): void {
		$result = $this->executeCodeSnippet(
			"doValue(Value('hello'));",
			valueDeclarations: "
				doValue = ^v: Either<String, Integer> => Either<Boolean, Integer> ::
					v->map(^s: String => Boolean :: s->length > 5);
			"
		);
		$this->assertEquals("Value(false)", $result);
	}

	public function testMapEitherValueTrue(): void {
		$result = $this->executeCodeSnippet(
			"doValue(Value('hello world!'));",
			valueDeclarations: "
				doValue = ^v: Either<String, Integer> => Either<Boolean, Integer> ::
					v->map(^s: String => Boolean :: s->length > 5);
			"
		);
		$this->assertEquals("Value(true)", $result);
	}

	public function testMapEitherError(): void {
		$result = $this->executeCodeSnippet(
			"doValue(@42);",
			valueDeclarations: "
				doValue = ^v: Either<String, Integer> => Either<Boolean, Integer> ::
					v->map(^s: String => Boolean :: s->length > 5);
			"
		);
		$this->assertEquals("@42", $result);
	}

	public function testMapEitherWithResultError(): void {
		$result = $this->executeCodeSnippet(
			"doValue(Value(''));",
			valueDeclarations: "
				doValue = ^v: Either<String, Integer> => Either<Boolean, Integer|Null> ::
					v->map(^s: String => Result<Boolean, Null> ::
						?whenValueOf(s) { '': @null, ~: s->length > 5 });
			"
		);
		$this->assertEquals("@null", $result);
	}

	public function testMapEitherWithResultNonEmpty(): void {
		$result = $this->executeCodeSnippet(
			"doValue(Value('hi!'));",
			valueDeclarations: "
				doValue = ^v: Either<String, Integer> => Either<Boolean, Integer|Null> ::
					v->map(^s: String => Result<Boolean, Null> ::
						?whenValueOf(s) { '': @null, ~: s->length > 5 });
			"
		);
		$this->assertEquals("Value(false)", $result);
	}

	public function testMapSubtype(): void {
		$result = $this->executeCodeSnippet(
			"r1(Value(15));",
			typeDeclarations: "
				X := Null;
			",
			valueDeclarations: "
				r1 = ^g: Value<Integer<10..20>> => Value<Integer<15..25>> ::
					g->map(^c: Integer<10..20> => Integer<15..25> :: c + 5);
			"
		);
		$this->assertEquals("Value(20)", $result);
	}

	public function testMapSubtypeWithResultError(): void {
		$result = $this->executeCodeSnippet(
			"r3(Value(10));",
			typeDeclarations: "
				X := Null;
			",
			valueDeclarations: "
				r3 = ^g: Value<Integer<10..20>> => Result<Value<Integer<15..25>>, X> ::
					g->map(^c: Integer<10..20> => Result<Integer<15..25>, X> ::
						?whenValueOf(c) { 10: @X!null, ~: c + 5 });
			"
		);
		$this->assertEquals("@X!null", $result);
	}

	public function testMapSubtypeWithResultOk(): void {
		$result = $this->executeCodeSnippet(
			"r3(Value(15));",
			typeDeclarations: "
				X := Null;
			",
			valueDeclarations: "
				r3 = ^g: Value<Integer<10..20>> => Result<Value<Integer<15..25>>, X> ::
					g->map(^c: Integer<10..20> => Result<Integer<15..25>, X> ::
						?whenValueOf(c) { 10: @X!null, ~: c + 5 });
			"
		);
		$this->assertEquals("Value(20)", $result);
	}

	// Error cases

	public function testMapInvalidParameterType(): void {
		$this->executeErrorCodeSnippet(
			"Invalid parameter type",
			"doValue(Value(42));",
			valueDeclarations: "
				doValue = ^v: Value<Integer> => Any ::
					v->map(5);
			"
		);
	}

	public function testMapIncompatibleCallbackParameterType(): void {
		$this->executeErrorCodeSnippet(
			"The value type Integer is not a subtype of the callback function parameter type Boolean",
			"doValue(Value(42));",
			valueDeclarations: "
				doValue = ^v: Value<Integer> => Any ::
					v->map(^b: Boolean => Boolean :: b);
			"
		);
	}

}
