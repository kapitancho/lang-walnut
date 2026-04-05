<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Value;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class FilterTest extends CodeExecutionTestHelper {

	public function testFilterValueOk(): void {
		$result = $this->executeCodeSnippet(
			"doValue(Value(3));",
			valueDeclarations: "
				doValue = ^a: Value<Integer<1..4>> => Optional<Value<Integer<1..4>>> ::
					a->filter(^item: Integer => Boolean :: item > 2);
			"
		);
		$this->assertEquals("Value(3)", $result);
	}

	public function testFilterValueEmpty(): void {
		$result = $this->executeCodeSnippet(
			"doValue(Value(2));",
			valueDeclarations: "
				doValue = ^a: Value<Integer<1..4>> => Optional<Value<Integer<1..4>>> ::
					a->filter(^item: Integer => Boolean :: item > 2);
			"
		);
		$this->assertEquals("empty", $result);
	}

	public function testFilterValueError(): void {
		$result = $this->executeCodeSnippet(
			"doValue(Value(3));",
			valueDeclarations: "
				doValue = ^a: Value<Integer<1..4>> => Result<Optional<Value<Integer<1..4>>>, Null> ::
					a->filter(^item: Integer => Result<Boolean, Null> :: @null);
			"
		);
		$this->assertEquals("@null", $result);
	}


	public function testFilterInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "Value(1)->filter(5);");
	}

	public function testFilterInvalidParameterParameterType(): void {
		$this->executeErrorCodeSnippet("The value type Integer[1] is not a subtype of the of the callback function parameter type Boolean",
			"Value(1)->filter(^Boolean => Boolean :: true);");
	}

	public function testFilterInvalidParameterReturnType(): void {
		$this->executeErrorCodeSnippet("The return type of the callback function must be a subtype of Result<Boolean, Any>, but got Real",
			"Value(1)->filter(^Any => Real :: 3.14);");
	}

}
