<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Result;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class FilterTest extends CodeExecutionTestHelper {

	public function testFilterArrayOk(): void {
		$result = $this->executeCodeSnippet(
			"doArray[2, 5, 3];",
			valueDeclarations: "
				doArray = ^a: Result<Array<Integer, 2..5>, Null> => Result<Array<Integer, ..5>, Null> ::
					a->filter(^item: Integer => Boolean :: item > 2);
			"
		);
		$this->assertEquals("[5, 3]", $result);
	}

	public function testFilterArrayNull(): void {
		$result = $this->executeCodeSnippet(
			"doArray(@null);",
			valueDeclarations: "
				doArray = ^a: Result<Array<Integer, 2..5>, Null> => Result<Array<Integer, ..5>, Null> ::
					a->filter(^item: Integer => Boolean :: item > 2);
			"
		);
		$this->assertEquals("@null", $result);
	}

	public function testFilterArrayUnionOk(): void {
		$result = $this->executeCodeSnippet(
			"doArray[2, 5, 3];",
			valueDeclarations: "
				doArray = ^a: Result<Array<Integer, 2..5>, Null> => Result<Array<Integer, ..5>, NotANumber|Null> ::
					a->filter(^item: Integer => Result<Boolean, NotANumber> :: (9 // item)? > 2);
			"
		);
		$this->assertEquals("[2, 3]", $result);
	}

	public function testFilterArrayUnionError(): void {
		$result = $this->executeCodeSnippet(
			"doArray[2, 0, 3];",
			valueDeclarations: "
				doArray = ^a: Result<Array<Integer, 2..5>, Null> => Result<Array<Integer, ..5>, NotANumber|Null> ::
					a->filter(^item: Integer => Result<Boolean, NotANumber> :: (9 // item)? > 2);
			"
		);
		$this->assertEquals("@NotANumber", $result);
	}

	// Map
	public function testFilterMapOk(): void {
		$result = $this->executeCodeSnippet(
			"doMap[a: 2, b: 5, c: 3];",
			valueDeclarations: "
				doMap = ^a: Result<Map<Integer, 2..5>, Null> => Result<Map<Integer, ..5>, Null> ::
					a->filter(^item: Integer => Boolean :: item > 2);
			"
		);
		$this->assertEquals("[b: 5, c: 3]", $result);
	}

	public function testFilterMapNull(): void {
		$result = $this->executeCodeSnippet(
			"doMap(@null);",
			valueDeclarations: "
				doMap = ^a: Result<Map<Integer, 2..5>, Null> => Result<Map<Integer, ..5>, Null> ::
					a->filter(^item: Integer => Boolean :: item > 2);
			"
		);
		$this->assertEquals("@null", $result);
	}

	public function testFilterMapUnionOk(): void {
		$result = $this->executeCodeSnippet(
			"doMap[a: 2, b: 5, c: 3];",
			valueDeclarations: "
				doMap = ^a: Result<Map<Integer, 2..5>, Null> => Result<Map<Integer, ..5>, NotANumber|Null> ::
					a->filter(^item: Integer => Result<Boolean, NotANumber> :: (9 // item)? > 2);
			"
		);
		$this->assertEquals("[a: 2, c: 3]", $result);
	}

	public function testFilterMapUnionError(): void {
		$result = $this->executeCodeSnippet(
			"doMap[a: 2, b: 0, c: 3];",
			valueDeclarations: "
				doMap = ^a: Result<Map<Integer, 2..5>, Null> => Result<Map<Integer, ..5>, NotANumber|Null> ::
					a->filter(^item: Integer => Result<Boolean, NotANumber> :: (9 // item)? > 2);
			"
		);
		$this->assertEquals("@NotANumber", $result);
	}

	// Set
	public function testFilterSetOk(): void {
		$result = $this->executeCodeSnippet(
			"doSet[2; 5; 3];",
			valueDeclarations: "
				doSet = ^a: Result<Set<Integer, 2..5>, Null> => Result<Set<Integer, ..5>, Null> ::
					a->filter(^item: Integer => Boolean :: item > 2);
			"
		);
		$this->assertEquals("[5; 3]", $result);
	}

	public function testFilterSetNull(): void {
		$result = $this->executeCodeSnippet(
			"doSet(@null);",
			valueDeclarations: "
				doSet = ^a: Result<Set<Integer, 2..5>, Null> => Result<Set<Integer, ..5>, Null> ::
					a->filter(^item: Integer => Boolean :: item > 2);
			"
		);
		$this->assertEquals("@null", $result);
	}

	public function testFilterSetUnionOk(): void {
		$result = $this->executeCodeSnippet(
			"doSet[2; 5; 3];",
			valueDeclarations: "
				doSet = ^a: Result<Set<Integer, 2..5>, Null> => Result<Set<Integer, ..5>, NotANumber|Null> ::
					a->filter(^item: Integer => Result<Boolean, NotANumber> :: (9 // item)? > 2);
			"
		);
		$this->assertEquals("[2; 3]", $result);
	}

	public function testFilterSetUnionError(): void {
		$result = $this->executeCodeSnippet(
			"doSet[2; 0; 3];",
			valueDeclarations: "
				doSet = ^a: Result<Set<Integer, 2..5>, Null> => Result<Set<Integer, ..5>, NotANumber|Null> ::
					a->filter(^item: Integer => Result<Boolean, NotANumber> :: (9 // item)? > 2);
			"
		);
		$this->assertEquals("@NotANumber", $result);
	}

	public function testInvalidType(): void {
		$this->executeErrorCodeSnippet(
			"Method 'filter' is not defined for type 'String'.",
			"doArray('hello');",
			valueDeclarations: "
				doArray = ^a: Result<String, Null> => Any ::
					a->filter(^item: Integer => Boolean :: item > 2);
			"
		);
	}

}
