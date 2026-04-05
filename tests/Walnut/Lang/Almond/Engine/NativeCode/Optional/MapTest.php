<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Optional;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class MapTest extends CodeExecutionTestHelper {

	// Array tests

	public function testMapArrayEmpty(): void {
		$result = $this->executeCodeSnippet(
			"doArray([]);",
			valueDeclarations: "
				doArray = ^a: Optional<Array<Integer>> => Optional<Array<Integer>> ::
					a->map(^i: Integer => Integer :: i + 3);
			"
		);
		$this->assertEquals("[]", $result);
	}

	public function testMapArrayNonEmpty(): void {
		$result = $this->executeCodeSnippet(
			"doArray[1, 2, 5, 10, 5];",
			valueDeclarations: "
				doArray = ^a: Optional<Array<Integer>> => Optional<Array<Integer>> ::
					a->map(^i: Integer => Integer :: i + 3);
			"
		);
		$this->assertEquals("[4, 5, 8, 13, 8]", $result);
	}

	public function testMapTuple(): void {
		$result = $this->executeCodeSnippet(
			"doArray[1, 2, 5, 10, 5];",
			valueDeclarations: "
				doArray = ^a: Optional<[Integer, Integer, ...Integer]> => Optional<Array<Integer>> ::
					a->map(^i: Integer => Integer :: i + 3);
			"
		);
		$this->assertEquals("[4, 5, 8, 13, 8]", $result);
	}

	public function testMapArrayWithNullError(): void {
		$result = $this->executeCodeSnippet(
			"doArray[1, 2];",
			valueDeclarations: "
				doArray = ^a: Optional<Array<Integer>> => Optional<Array<Integer>> ::
					a->map(^i: Integer => Integer :: i + 3);
			"
		);
		$this->assertEquals("[4, 5]", $result);
	}

	public function testMapArrayResultErrorEmpty(): void {
		$result = $this->executeCodeSnippet(
			"doArray(empty);",
			valueDeclarations: "
				doArray = ^a: Optional<Array<Integer>> => Optional<Array<Integer>> ::
					a->map(^i: Integer => Integer :: i + 3);
			"
		);
		$this->assertEquals("empty", $result);
	}

	// Map tests

	public function testMapMapEmpty(): void {
		$result = $this->executeCodeSnippet(
			"doMap([:]);",
			valueDeclarations: "
				doMap = ^m: Optional<Map<Integer>> => Optional<Map<Integer>> ::
					m->map(^i: Integer => Integer :: i + 3);
			"
		);
		$this->assertEquals("[:]", $result);
	}

	public function testMapMapNonEmpty(): void {
		$result = $this->executeCodeSnippet(
			"doMap[a: 1, b: 2, c: 5, d: 10, e: 5];",
			valueDeclarations: "
				doMap = ^m: Optional<Map<Integer>> => Optional<Map<Integer>> ::
					m->map(^i: Integer => Integer :: i + 3);
			"
		);
		$this->assertEquals("[a: 4, b: 5, c: 8, d: 13, e: 8]", $result);
	}

	public function testMapRecord(): void {
		$result = $this->executeCodeSnippet(
			"doMap[a: 1, b: 2, c: 5, d: 10, e: 5];",
			valueDeclarations: "
				doMap = ^m: Optional<[a: Integer, b: Integer, ... Integer]> => Optional<Map<Integer>> ::
					m->map(^i: Integer => Integer :: i + 3);
			"
		);
		$this->assertEquals("[a: 4, b: 5, c: 8, d: 13, e: 8]", $result);
	}

	public function testMapMapWithNullError(): void {
		$result = $this->executeCodeSnippet(
			"doMap[a: 1, b: 2];",
			valueDeclarations: "
				doMap = ^m: Optional<Map<Integer>> => Optional<Map<Integer>> ::
					m->map(^i: Integer => Integer :: i + 3);
			"
		);
		$this->assertEquals("[a: 4, b: 5]", $result);
	}

	public function testMapMapResultErrorEmpty(): void {
		$result = $this->executeCodeSnippet(
			"doMap(empty);",
			valueDeclarations: "
				doMap = ^m: Optional<Map<Integer>> => Optional<Map<Integer>> ::
					m->map(^i: Integer => Integer :: i + 3);
			"
		);
		$this->assertEquals("empty", $result);
	}

	public function testMapMapKeyType(): void {
		$result = $this->executeCodeSnippet(
			"doMap[a: 1, b: 2];",
			valueDeclarations: "
				doMap = ^m: Optional<Map<String<1>:Integer>> => Optional<Map<String<1>:Integer>> ::
					m->map(^i: Integer => Integer :: i + 3);
			"
		);
		$this->assertEquals("[a: 4, b: 5]", $result);
	}

	// Set tests

	public function testMapSetEmpty(): void {
		$result = $this->executeCodeSnippet(
			"doSet([;]);",
			valueDeclarations: "
				doSet = ^s: Optional<Set<Integer>> => Optional<Set<Integer>> ::
					s->map(^i: Integer => Integer :: i + 3);
			"
		);
		$this->assertEquals("[;]", $result);
	}

	public function testMapSetNonEmpty(): void {
		$result = $this->executeCodeSnippet(
			"doSet[1; 2; 5; 10; 5];",
			valueDeclarations: "
				doSet = ^s: Optional<Set<Integer>> => Optional<Set<Integer>> ::
					s->map(^i: Integer => Integer :: i + 3);
			"
		);
		$this->assertEquals("[4; 5; 8; 13]", $result);
	}

	public function testMapSetNonUnique(): void {
		$result = $this->executeCodeSnippet(
			"doSet['hello'; 'world'; 'hi'];",
			valueDeclarations: "
				doSet = ^s: Optional<Set<String>> => Optional<Set<Integer>> ::
					s->map(^st: String => Integer :: st->length);
			"
		);
		$this->assertEquals("[5; 2]", $result);
	}

	public function testMapSetWithNullError(): void {
		$result = $this->executeCodeSnippet(
			"doSet[1; 2];",
			valueDeclarations: "
				doSet = ^s: Optional<Set<Integer>> => Optional<Set<Integer>> ::
					s->map(^i: Integer => Integer :: i + 3);
			"
		);
		$this->assertEquals("[4; 5]", $result);
	}

	public function testMapSetResultErrorEmpty(): void {
		$result = $this->executeCodeSnippet(
			"doSet(empty);",
			valueDeclarations: "
				doSet = ^s: Optional<Set<Integer>> => Optional<Set<Integer>> ::
					s->map(^i: Integer => Integer :: i + 3);
			"
		);
		$this->assertEquals("empty", $result);
	}

	// Value tests

	public function testMapValueOk(): void {
		$result = $this->executeCodeSnippet(
			"doValue(Value(42));",
			valueDeclarations: "
				doValue = ^v: Optional<Value<Integer>> => Optional<Value<Integer>> ::
					v->map(^x: Integer => Integer :: x + 3);
			"
		);
		$this->assertEquals("Value(45)", $result);
	}

	public function testMapValueEmpty(): void {
		$result = $this->executeCodeSnippet(
			"doValue(empty);",
			valueDeclarations: "
				doValue = ^v: Optional<Value<Integer>> => Optional<Value<Integer>> ::
					v->map(^x: Integer => Integer :: x + 3);
			"
		);
		$this->assertEquals("empty", $result);
	}

	// Error cases

	public function testMapArrayInvalidReturnType(): void {
		$this->executeErrorCodeSnippet(
			"Function body return type 'Optional<Array<Integer>>' is not compatible with declared return type 'Array<Integer>'.",
			"doMap([]);",
			valueDeclarations: "
				doMap = ^a: Optional<Array<Integer>> => Array<Integer> ::
					a->map(^i: Integer => Integer :: i + 3);
			"
		);
	}

	public function testMapMapInvalidReturnType(): void {
		$this->executeErrorCodeSnippet(
			"Function body return type 'Optional<Map<Integer>>' is not compatible with declared return type 'Map<Integer>'.",
			"doMap([:]);",
			valueDeclarations: "
				doMap = ^m: Optional<Map<Integer>> => Map<Integer> ::
					m->map(^i: Integer => Integer :: i + 3);
			"
		);
	}

	public function testMapArrayInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type',
			"doMap([]);",
			valueDeclarations: "
				doMap = ^a: Optional<Array<Integer>> => Optional<Array<Integer>> ::
					a->map(5);
			"
		);
	}

	public function testMapMapInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type',
			"doMap([:]);",
			valueDeclarations: "
				doMap = ^m: Optional<Map<Integer>> => Optional<Map<Integer>> ::
					m->map(5);
			"
		);
	}

	public function testMapArrayInvalidParameterParameterType(): void {
		$this->executeErrorCodeSnippet("The item type Integer is not a subtype of the of the callback function parameter type Boolean",
			"doMap([1, 2, 5, 10]);",
			valueDeclarations: "
				doMap = ^a: Optional<Array<Integer>> => Optional<Array<Integer>> ::
					a->map(^Boolean => Boolean :: true);
			"
		);
	}

	public function testMapMapInvalidParameterParameterType(): void {
		$this->executeErrorCodeSnippet("The item type Integer is not a subtype of the of the callback function parameter type Boolean",
			"doMap([a: 1, b: 2]);",
			valueDeclarations: "
				doMap = ^m: Optional<Map<Integer>> => Optional<Map<Integer>> ::
					m->map(^Boolean => Boolean :: true);
			"
		);
	}

	public function testMapSetInvalidReturnType(): void {
		$this->executeErrorCodeSnippet(
			"Function body return type 'Optional<Set<Integer>>",
			"doSet([;]);",
			valueDeclarations: "
				doSet = ^s: Optional<Set<Integer>> => Set<Integer> ::
					s->map(^i: Integer => Integer :: i + 3);
			"
		);
	}

	public function testMapSetInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type',
			"doSet([;]);",
			valueDeclarations: "
				doSet = ^s: Optional<Set<Integer>> => Optional<Set<Integer>> ::
					s->map(5);
			"
		);
	}

	public function testMapSetInvalidParameterParameterType(): void {
		$this->executeErrorCodeSnippet("The item type Integer is not a subtype of the of the callback function parameter type Boolean",
			"doSet[1; 2];",
			valueDeclarations: "
				doSet = ^s: Optional<Set<Integer>> => Optional<Set<Integer>> ::
					s->map(^Boolean => Boolean :: true);
			"
		);
	}

}