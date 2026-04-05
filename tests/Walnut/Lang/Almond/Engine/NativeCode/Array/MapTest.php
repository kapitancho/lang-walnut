<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Array;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class MapTest extends CodeExecutionTestHelper {

	public function testMapEmpty(): void {
		$result = $this->executeCodeSnippet("[]->map(^Integer => Integer :: # + 3);");
		$this->assertEquals("[]", $result);
	}

	public function testMapNonEmpty(): void {
		$result = $this->executeCodeSnippet("[1, 2, 5, 10, 5]->map(^Integer => Integer :: # + 3);");
		$this->assertEquals("[4, 5, 8, 13, 8]", $result);
	}

	public function testMapNonEmptyEmptyValue(): void {
		$result = $this->executeCodeSnippet("[1, 2, 5, 10, 5]->map(^Integer => Optional<Integer> :: {
			x = # + 3;
			?whenTypeOf(x) { `Integer<10..>: empty, ~: x }
		});");
		$this->assertEquals("[4, 5, 8, 8]", $result);
	}

	public function testMapNonEmptyError(): void {
		$result = $this->executeCodeSnippet("[1, 2, 5, 10, 5]->map(^Integer => Result<Integer, String> :: @'error');");
		$this->assertEquals("@'error'", $result);
	}

	public function testMapReturnTypeNoError(): void {
		$result = $this->executeCodeSnippet("fn[1, 2, 5]",
			valueDeclarations: "
				fn = ^p: Array<Integer, 2..5> => Array<Integer, 2..5> ::
					p->map(^v: Integer => Integer :: v + 1);
			"
		);
		$this->assertEquals("[2, 3, 6]", $result);
	}

	public function testMapReturnTypeNoErrorOptional(): void {
		$result = $this->executeCodeSnippet("fn[1, 2, 5]",
			valueDeclarations: "
				fn = ^p: Array<Integer, 2..5> => Array<Integer, ..5> ::
					p->map(^v: Integer => Optional<Integer> :: {
						?whenTypeOf(v) { `Integer<5..>: empty, ~: v + 1 }
					});
			"
		);
		$this->assertEquals("[2, 3]", $result);
	}

	public function testMapReturnTypeResultNoError(): void {
		$result = $this->executeCodeSnippet("fn[1, 2, 5]",
			valueDeclarations: "
				fn = ^p: Array<Integer, 2..5> => Result<Array<Integer, 2..5>, String> ::
					p->map(^v: Integer => Result<Integer, String> :: v + 1);
			"
		);
		$this->assertEquals("[2, 3, 6]", $result);
	}

	public function testMapReturnTypeResultNoErrorEmpty(): void {
		$result = $this->executeCodeSnippet("fn[1, 2, 5]",
			valueDeclarations: "
				fn = ^p: Array<Integer, 2..5> => Result<Array<Integer, ..5>, String> ::
					p->map(^v: Integer => Result<Optional<Integer>, String> :: {
						?whenTypeOf(v) { `Integer<5..>: empty, ~: v + 1 }
					});
			"
		);
		$this->assertEquals("[2, 3]", $result);
	}

	public function testMapReturnTypeResultError(): void {
		$result = $this->executeCodeSnippet("fn[1, 2, 5]",
			valueDeclarations: "
				fn = ^p: Array<Integer, 2..5> => Result<Array<Integer, 2..5>, String> ::
					p->map(^v: Integer => Result<Integer, String> :: @'error');
			"
		);
		$this->assertEquals("@'error'", $result);
	}

	public function testMapInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "[1, 'a']->map(5);");
	}

	public function testMapInvalidParameterParameterType(): void {
		$this->executeErrorCodeSnippet("The item type (Integer[1]|String['a']) is not a subtype of the of the callback function parameter type Boolean",
			"[1, 'a']->map(^Boolean => Boolean :: true);");
	}

}