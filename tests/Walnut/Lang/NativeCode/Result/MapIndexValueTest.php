<?php

namespace Walnut\Lang\Test\NativeCode\Result;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class MapIndexValueTest extends CodeExecutionTestHelper {

	public function testMapIndexValueEmpty(): void {
		$result = $this->executeCodeSnippet(
			"doArray([]);",
			valueDeclarations: "
				doArray = ^a: Result<Array<Integer>, Null> => Result<Array<Integer>, Null> ::
					a->mapIndexValue(^[index: Integer, value: Integer] => Integer :: #index + #value);
			"
		);
		$this->assertEquals("[]", $result);
	}

	public function testMapIndexValueNonEmpty(): void {
		$result = $this->executeCodeSnippet(
			"doArray[1, 2, 5, 10, 5];",
			valueDeclarations: "
				doArray = ^a: Result<Array<Integer>, Null> => Result<Array<Integer>, Null> ::
					a->mapIndexValue(^[index: Integer, value: Integer] => Integer :: #index + #value);
			"
		);
		$this->assertEquals("[1, 3, 7, 13, 9]", $result);
	}

	public function testMapIndexValueNonEmptyCallbackError(): void {
		$result = $this->executeCodeSnippet(
			"doArray[1, 2, 5, 10, 5];",
			valueDeclarations: "
				doArray = ^a: Result<Array<Integer>, Null> => Result<Array<Integer>, Null|String> ::
					a->mapIndexValue(^[index: Integer, value: Integer] => Result<Integer, String> :: @'error');
			"
		);
		$this->assertEquals("@'error'", $result);
	}

	public function testMapIndexValueWithResultError(): void {
		$result = $this->executeCodeSnippet(
			"doArray(@'initial_error');",
			valueDeclarations: "
				doArray = ^a: Result<Array<Integer>, String> => Result<Array<Integer>, String> ::
					a->mapIndexValue(^[index: Integer, value: Integer] => Integer :: #index + #value);
			"
		);
		$this->assertEquals("@'initial_error'", $result);
	}

	public function testMapIndexValueWithResultErrorAndCallbackError(): void {
		$result = $this->executeCodeSnippet(
			"doArray(@'first_error');",
			valueDeclarations: "
				doArray = ^a: Result<Array<Integer>, String> => Result<Array<Integer>, String|Integer> ::
					a->mapIndexValue(^[index: Integer, value: Integer] => Result<Integer, Integer> :: @999);
			"
		);
		$this->assertEquals("@'first_error'", $result);
	}

	public function testMapIndexValueWithNullError(): void {
		$result = $this->executeCodeSnippet(
			"doArray[1, 2];",
			valueDeclarations: "
				doArray = ^a: Result<Array<Integer>, Null> => Result<Array<Integer>, Null> ::
					a->mapIndexValue(^[index: Integer, value: Integer] => Integer :: #index + #value);
			"
		);
		$this->assertEquals("[1, 3]", $result);
	}

	public function testMapIndexValueResultErrorNull(): void {
		$result = $this->executeCodeSnippet(
			"doArray(@null);",
			valueDeclarations: "
				doArray = ^a: Result<Array<Integer>, Null> => Result<Array<Integer>, Null> ::
					a->mapIndexValue(^[index: Integer, value: Integer] => Integer :: #index + #value);
			"
		);
		$this->assertEquals("@null", $result);
	}

	public function testMapIndexValueInvalidReturnType(): void {
		$this->executeErrorCodeSnippet(
			"Expected a return value of type Array<Integer>, got Result<Array<Integer>, Null>",
			"doArray([]);",
			valueDeclarations: "
				doArray = ^a: Result<Array<Integer>, Null> => Array<Integer> ::
					a->mapIndexValue(^[index: Integer, value: Integer] => Integer :: #index + #value);
			"
		);
	}

	public function testMapIndexValueInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type',
			"doArray([]);",
			valueDeclarations: "
				doArray = ^a: Result<Array<Integer>, Null> => Result<Array<Integer>, Null> ::
					a->mapIndexValue(5);
			"
		);
	}

	public function testMapIndexValueInvalidParameterParameterType(): void {
		$this->executeErrorCodeSnippet("of the callback function is not a subtype of",
			"doArray[1, 2, 5, 10, 5];",
			valueDeclarations: "
				doArray = ^a: Result<Array<Integer>, Null> => Result<Array<Integer>, Null> ::
					a->mapIndexValue(^[index: Integer] => Integer :: #index + 3);
			"
		);
	}

}
