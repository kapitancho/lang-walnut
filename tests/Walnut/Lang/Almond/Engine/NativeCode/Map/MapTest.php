<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Map;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class MapTest extends CodeExecutionTestHelper {

	public function testMapEmpty(): void {
		$result = $this->executeCodeSnippet("[:]->map(^i: Integer => Integer :: i + 3);");
		$this->assertEquals("[:]", $result);
	}

	public function testMapNonEmpty(): void {
		$result = $this->executeCodeSnippet("[a: 1, b: 2, c: 5, d: 10, e: 5]->map(^i: Integer => Integer :: i + 3);");
		$this->assertEquals("[a: 4, b: 5, c: 8, d: 13, e: 8]", $result);
	}

	public function testMapNonEmptyEmptyValue(): void {
		$result = $this->executeCodeSnippet("[a: 1, b: 2, c: 5, d: 10, e: 5]->map(^i: Integer => Optional<Integer> :: {
			x = i + 3;
			?whenTypeOf(x) { `Integer<10..>: empty, ~: x }
		});");
		$this->assertEquals("[a: 4, b: 5, c: 8, e: 8]", $result);
	}

	public function testMapNonEmptyError(): void {
		$result = $this->executeCodeSnippet("[a: 1, b: 2, c: 5, d: 10, e: 5]->map(^Integer => Result<Integer, String> :: @'error');");
		$this->assertEquals("@'error'", $result);
	}

	public function testMapKeyType(): void {
		$result = $this->executeCodeSnippet(
			"fn[a: 1, b: 2];",
			valueDeclarations: "fn = ^m: Map<String<1>:Integer> => Map<String<1>:Boolean> :: 
				m->map(^v: Integer => Boolean :: v > 1);"
		);
		$this->assertEquals("[a: false, b: true]", $result);
	}

	public function testMapReturnTypeNoError(): void {
		$result = $this->executeCodeSnippet("fn[a: 1, b: 2, c: 5]",
			valueDeclarations: "
				fn = ^m: Map<String<1..3>:Integer, 2..5> => Map<String<1..3>:Integer, 2..5> ::
					m->map(^v: Integer => Integer :: v + 1);
			"
		);
		$this->assertEquals("[a: 2, b: 3, c: 6]", $result);
	}

	public function testMapReturnTypeNoErrorOptional(): void {
		$result = $this->executeCodeSnippet("fn[a: 1, b: 2, c: 5]",
			valueDeclarations: "
				fn = ^m: Map<String<1..3>:Integer, 2..5> => Map<String<1..3>:Integer, ..5> ::
					m->map(^v: Integer => Optional<Integer> :: {
						?whenTypeOf(v) { `Integer<5..>: empty, ~: v + 1 }
					});
			"
		);
		$this->assertEquals("[a: 2, b: 3]", $result);
	}

	public function testMapReturnTypeResultNoError(): void {
		$result = $this->executeCodeSnippet("fn[a: 1, b: 2, c: 5]",
			valueDeclarations: "
				fn = ^m: Map<String<1..3>:Integer, 2..5> => Result<Map<String<1..3>:Integer, 2..5>, String> ::
					m->map(^v: Integer => Result<Integer, String> :: v + 1);
			"
		);
		$this->assertEquals("[a: 2, b: 3, c: 6]", $result);
	}

	public function testMapReturnTypeResultNoErrorEmpty(): void {
		$result = $this->executeCodeSnippet("fn[a: 1, b: 2, c: 5]",
			valueDeclarations: "
				fn = ^m: Map<String<1..3>:Integer, 2..5> => Result<Map<String<1..3>:Integer, ..5>, String> ::
					m->map(^v: Integer => Result<Optional<Integer>, String> :: {
						?whenTypeOf(v) { `Integer<5..>: empty, ~: v + 1 }
					});
			"
		);
		$this->assertEquals("[a: 2, b: 3]", $result);
	}

	public function testMapReturnTypeResultError(): void {
		$result = $this->executeCodeSnippet("fn[a: 1, b: 2, c: 5]",
			valueDeclarations: "
				fn = ^m: Map<String<1..3>:Integer, 2..5> => Result<Map<String<1..3>:Integer, 2..5>, String> ::
					m->map(^v: Integer => Result<Integer, String> :: @'error');
			"
		);
		$this->assertEquals("@'error'", $result);
	}

	public function testMapInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "[a: 1, b: 'a']->map(5);");
	}

	public function testMapInvalidParameterParameterType(): void {
		$this->executeErrorCodeSnippet("The item type (Integer[1]|String['a']) is not a subtype of the of the callback function parameter type Boolean",
			"[a: 1, b: 'a']->map(^Boolean => Boolean :: true);");
	}

}