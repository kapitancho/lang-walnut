<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Optional;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class FilterTest extends CodeExecutionTestHelper {

	public function testFilterArrayOk(): void {
		$result = $this->executeCodeSnippet(
			"doArray[2, 5, 3];",
			valueDeclarations: "
				doArray = ^a: Optional<Array<Integer, 2..5>> => Optional<Array<Integer, ..5>> ::
					a->filter(^item: Integer => Boolean :: item > 2);
			"
		);
		$this->assertEquals("[5, 3]", $result);
	}

	public function testFilterArrayEmpty(): void {
		$result = $this->executeCodeSnippet(
			"doArray(empty);",
			valueDeclarations: "
				doArray = ^a: Optional<Array<Integer, 2..5>> => Optional<Array<Integer, ..5>> ::
					a->filter(^item: Integer => Boolean :: item > 2);
			"
		);
		$this->assertEquals("empty", $result);
	}

	// Map
	public function testFilterMapOk(): void {
		$result = $this->executeCodeSnippet(
			"doMap[a: 2, b: 5, c: 3];",
			valueDeclarations: "
				doMap = ^a: Optional<Map<Integer, 2..5>> => Optional<Map<Integer, ..5>> ::
					a->filter(^item: Integer => Boolean :: item > 2);
			"
		);
		$this->assertEquals("[b: 5, c: 3]", $result);
	}

	public function testFilterMapEmpty(): void {
		$result = $this->executeCodeSnippet(
			"doMap(empty);",
			valueDeclarations: "
				doMap = ^a: Optional<Map<Integer, 2..5>> => Optional<Map<Integer, ..5>> ::
					a->filter(^item: Integer => Boolean :: item > 2);
			"
		);
		$this->assertEquals("empty", $result);
	}

	// Set
	public function testFilterSetOk(): void {
		$result = $this->executeCodeSnippet(
			"doSet[2; 5; 3];",
			valueDeclarations: "
				doSet = ^a: Optional<Set<Integer, 2..5>> => Optional<Set<Integer, ..5>> ::
					a->filter(^item: Integer => Boolean :: item > 2);
			"
		);
		$this->assertEquals("[5; 3]", $result);
	}

	public function testFilterSetEmpty(): void {
		$result = $this->executeCodeSnippet(
			"doSet(empty);",
			valueDeclarations: "
				doSet = ^a: Optional<Set<Integer, 2..5>> => Optional<Set<Integer, ..5>> ::
					a->filter(^item: Integer => Boolean :: item > 2);
			"
		);
		$this->assertEquals("empty", $result);
	}

	public function testFilterValueOk(): void {
		$result = $this->executeCodeSnippet(
			"doValue(Value(3));",
			valueDeclarations: "
				doValue = ^a: Optional<Value<Integer<1..4>>> => Optional<Value<Integer<1..4>>> ::
					a->filter(^item: Integer => Boolean :: item > 2);
			"
		);
		$this->assertEquals("Value(3)", $result);
	}

	public function testFilterValueEmpty(): void {
		$result = $this->executeCodeSnippet(
			"doValue(Value(2));",
			valueDeclarations: "
				doValue = ^a: Optional<Value<Integer<1..4>>> => Optional<Value<Integer<1..4>>> ::
					a->filter(^item: Integer => Boolean :: item > 2);
			"
		);
		$this->assertEquals("empty", $result);
	}

	public function testFilterValueNull(): void {
		$result = $this->executeCodeSnippet(
			"doValue(empty);",
			valueDeclarations: "
				doValue = ^a: Optional<Value<Integer<1..4>>> => Optional<Value<Integer<1..4>>> ::
					a->filter(^item: Integer => Boolean :: item > 2);
			"
		);
		$this->assertEquals("empty", $result);
	}

	/*
	public function testFilterValueError(): void {
		$result = $this->executeCodeSnippet(
			"doValue(Value(3));",
			valueDeclarations: "
				doValue = ^a: Optional<Value<Integer<1..4>>> => Optional<Result<Value<Integer<1..4>>, Null>> ::
					a->filter(^item: Integer => Result<Boolean, Null> :: @null);
			"
		);
		$this->assertEquals("@null", $result);
	}

	public function testFilterValueErrorEmpty(): void {
		$result = $this->executeCodeSnippet(
			"doValue(empty);",
			valueDeclarations: "
				doValue = ^a: Optional<Value<Integer<1..4>>> => Optional<Result<Value<Integer<1..4>>, Null>> ::
					a->filter(^item: Integer => Result<Boolean, Null> :: @null);
			"
		);
		$this->assertEquals("empty", $result);
	}
	*/

	public function testInvalidType(): void {
		$this->executeErrorCodeSnippet(
			"Method 'filter' is not defined for type 'String'.",
			"doArray('hello');",
			valueDeclarations: "
				doArray = ^a: Optional<String> => Any ::
					a->filter(^item: Integer => Boolean :: item > 2);
			"
		);
	}

}