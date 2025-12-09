<?php

namespace Walnut\Lang\NativeCode\Result;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class ReduceTest extends CodeExecutionTestHelper {

	public function testReduceOk(): void {
		$result = $this->executeCodeSnippet(
			"doArray[2, 5, 3];",
			valueDeclarations: "
				doArray = ^a: Result<Array<Integer>, Null> => Result<Integer, Null> ::
					a->reduce[initial: 0, reducer: ^[result: Integer, item: Integer] => Integer :: #result + #item];
			"
		);
		$this->assertEquals("10", $result);
	}

	public function testReduceError(): void {
		$result = $this->executeCodeSnippet(
			"doArray(@null);",
			valueDeclarations: "
				doArray = ^a: Result<Array<Integer>, Null> => Result<Integer, Null> ::
					a->reduce[initial: 0, reducer: ^[result: Integer, item: Integer] => Integer :: #result + #item];
			"
		);
		$this->assertEquals("@null", $result);
	}
	public function testReduceUnionOk(): void {
		$result = $this->executeCodeSnippet(
			"doArray[2, 5, 3];",
			valueDeclarations: "
				doArray = ^a: Result<Array<Integer>, Null> => Result<Integer, Null|NotANumber> ::
					a->reduce[initial: 0, reducer: ^[result: Integer, item: Integer] => Result<Integer, NotANumber> :: #result + ?noError(10 // #item)];
			"
		);
		$this->assertEquals("10", $result);
	}
	public function testReduceUnionError(): void {
		$result = $this->executeCodeSnippet(
			"doArray[2, 0, 3];",
			valueDeclarations: "
				doArray = ^a: Result<Array<Integer>, Null> => Result<Integer, Null|NotANumber> ::
					a->reduce[initial: 0, reducer: ^[result: Integer, item: Integer] => Result<Integer, NotANumber> :: #result + ?noError(10 // #item)];
			"
		);
		$this->assertEquals("@NotANumber", $result);
	}

	public function testInvalidType(): void {
		$this->executeErrorCodeSnippet(
			"Invalid target type: Result<String, Null>",
			"doArray('hello');",
			valueDeclarations: "
				doArray = ^a: Result<String, Null> => Any ::
					a->reduce(^item: Integer => Boolean :: item > 2);
			"
		);
	}

}
