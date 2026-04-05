<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Array;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class MapIndexValueTest extends CodeExecutionTestHelper {

	public function testMapIndexValueEmpty(): void {
		$result = $this->executeCodeSnippet("[]->mapIndexValue(^[index: Integer, value: Integer] => Integer :: #index + #value);");
		$this->assertEquals("[]", $result);
	}

	public function testMapIndexValueNonEmpty(): void {
		$result = $this->executeCodeSnippet("[1, 2, 5, 10, 5]->mapIndexValue(^[index: Integer, value: Integer] => Integer :: #index + #value);");
		$this->assertEquals("[1, 3, 7, 13, 9]", $result);
	}

	public function testMapIndexValueNonEmptyEmptyValue(): void {
		$result = $this->executeCodeSnippet("[1, 2, 5, 10, 5]->mapIndexValue(^[index: Integer, value: Integer] => Optional<Integer> :: {
			x = #index + #value;
			?whenTypeOf(x) { `Integer<10..>: empty, ~: x }
		});");
		$this->assertEquals("[1, 3, 7, 9]", $result);
	}

	public function testMapIndexValueNonEmptyError(): void {
		$result = $this->executeCodeSnippet("[1, 2, 5, 10, 5]->mapIndexValue(^[index: Integer, value: Integer] => Result<Integer, String> :: @'error');");
		$this->assertEquals("@'error'", $result);
	}

	public function testMapIndexValueReturnTypeNoError(): void {
		$result = $this->executeCodeSnippet("fn[1, 2, 5]",
			valueDeclarations: "
				fn = ^p: Array<Integer, 2..5> => Array<Integer, 2..5> ::
					p->mapIndexValue(^[index: Integer<0..4>, value: Integer] => Integer :: #index + #value);
			"
		);
		$this->assertEquals("[1, 3, 7]", $result);
	}

	public function testMapIndexValueReturnTypeNoErrorOptional(): void {
		$result = $this->executeCodeSnippet("fn[1, 2, 5]",
			valueDeclarations: "
				fn = ^p: Array<Integer, 2..5> => Array<Integer, ..5> ::
					p->mapIndexValue(^[index: Integer<0..4>, value: Integer] => Optional<Integer> :: {
						?whenTypeOf(#value) { `Integer<5..>: empty, ~: #index + #value }
					});
			"
		);
		$this->assertEquals("[1, 3]", $result);
	}

	public function testMapIndexValueReturnTypeResultNoError(): void {
		$result = $this->executeCodeSnippet("fn[1, 2, 5]",
			valueDeclarations: "
				fn = ^p: Array<Integer, 2..5> => Result<Array<Integer, 2..5>, String> ::
					p->mapIndexValue(^[index: Integer<0..4>, value: Integer] => Result<Integer, String> :: #index + #value);
			"
		);
		$this->assertEquals("[1, 3, 7]", $result);
	}

	public function testMapIndexValueReturnTypeResultNoErrorEmpty(): void {
		$result = $this->executeCodeSnippet("fn[1, 2, 5]",
			valueDeclarations: "
				fn = ^p: Array<Integer, 2..5> => Result<Array<Integer, ..5>, String> ::
					p->mapIndexValue(^[index: Integer<0..4>, value: Integer] => Result<Optional<Integer>, String> :: {
						?whenTypeOf(#value) { `Integer<5..>: empty, ~: #index + #value }
					});
			"
		);
		$this->assertEquals("[1, 3]", $result);
	}

	public function testMapIndexValueReturnTypeResultError(): void {
		$result = $this->executeCodeSnippet("fn[1, 2, 5]",
			valueDeclarations: "
				fn = ^p: Array<Integer, 2..5> => Result<Array<Integer, 2..5>, String> ::
					p->mapIndexValue(^[index: Integer<0..4>, value: Integer] => Result<Integer, String> :: @'error');
			"
		);
		$this->assertEquals("@'error'", $result);
	}

	public function testMapIndexValueInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "[1, 'a']->mapIndexValue(5);");
	}

	public function testMapIndexValueInvalidParameterParameterType(): void {
		$this->executeErrorCodeSnippet("of the callback function is not a subtype of",
			"[1, 2, 5, 10, 5]->mapIndexValue(^[index: Integer] => Integer :: #index + 3);");
	}

}