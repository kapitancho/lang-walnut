<?php

namespace Walnut\Lang\Test\NativeCode\Result;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class MapTest extends CodeExecutionTestHelper {

	// Array tests

	public function testMapArrayEmpty(): void {
		$result = $this->executeCodeSnippet(
			"doArray([]);",
			valueDeclarations: "
				doArray = ^a: Result<Array<Integer>, Null> => Result<Array<Integer>, Null> ::
					a->map(^i: Integer => Integer :: i + 3);
			"
		);
		$this->assertEquals("[]", $result);
	}

	public function testMapArrayNonEmpty(): void {
		$result = $this->executeCodeSnippet(
			"doArray[1, 2, 5, 10, 5];",
			valueDeclarations: "
				doArray = ^a: Result<Array<Integer>, Null> => Result<Array<Integer>, Null> ::
					a->map(^i: Integer => Integer :: i + 3);
			"
		);
		$this->assertEquals("[4, 5, 8, 13, 8]", $result);
	}

	public function testMapTuple(): void {
		$result = $this->executeCodeSnippet(
			"doArray[1, 2, 5, 10, 5];",
			valueDeclarations: "
				doArray = ^a: Result<[Integer, Integer, ...Integer], Null> => Result<Array<Integer>, Null> ::
					a->map(^i: Integer => Integer :: i + 3);
			"
		);
		$this->assertEquals("[4, 5, 8, 13, 8]", $result);
	}

	public function testMapArrayNonEmptyCallbackError(): void {
		$result = $this->executeCodeSnippet(
			"doArray[1, 2, 5, 10, 5];",
			valueDeclarations: "
				doArray = ^a: Result<Array<Integer>, Null> => Result<Array<Integer>, Null|String> ::
					a->map(^Integer => Result<Integer, String> :: @'error');
			"
		);
		$this->assertEquals("@'error'", $result);
	}

	public function testMapArrayWithResultError(): void {
		$result = $this->executeCodeSnippet(
			"doArray(@'initial_error');",
			valueDeclarations: "
				doArray = ^a: Result<Array<Integer>, String> => Result<Array<Integer>, String> ::
					a->map(^i: Integer => Integer :: i + 3);
			"
		);
		$this->assertEquals("@'initial_error'", $result);
	}

	public function testMapArrayWithResultErrorAndCallbackError(): void {
		$result = $this->executeCodeSnippet(
			"doArray(@'first_error');",
			valueDeclarations: "
				doArray = ^a: Result<Array<Integer>, String> => Result<Array<Integer>, String|Integer> ::
					a->map(^Integer => Result<Integer, Integer> :: @999);
			"
		);
		$this->assertEquals("@'first_error'", $result);
	}

	public function testMapArrayWithNullError(): void {
		$result = $this->executeCodeSnippet(
			"doArray[1, 2];",
			valueDeclarations: "
				doArray = ^a: Result<Array<Integer>, Null> => Result<Array<Integer>, Null> ::
					a->map(^i: Integer => Integer :: i + 3);
			"
		);
		$this->assertEquals("[4, 5]", $result);
	}

	public function testMapArrayResultErrorNull(): void {
		$result = $this->executeCodeSnippet(
			"doArray(@null);",
			valueDeclarations: "
				doArray = ^a: Result<Array<Integer>, Null> => Result<Array<Integer>, Null> ::
					a->map(^i: Integer => Integer :: i + 3);
			"
		);
		$this->assertEquals("@null", $result);
	}

	// Map tests

	public function testMapMapEmpty(): void {
		$result = $this->executeCodeSnippet(
			"doMap([:]);",
			valueDeclarations: "
				doMap = ^m: Result<Map<Integer>, Null> => Result<Map<Integer>, Null> ::
					m->map(^i: Integer => Integer :: i + 3);
			"
		);
		$this->assertEquals("[:]", $result);
	}

	public function testMapMapNonEmpty(): void {
		$result = $this->executeCodeSnippet(
			"doMap[a: 1, b: 2, c: 5, d: 10, e: 5];",
			valueDeclarations: "
				doMap = ^m: Result<Map<Integer>, Null> => Result<Map<Integer>, Null> ::
					m->map(^i: Integer => Integer :: i + 3);
			"
		);
		$this->assertEquals("[a: 4, b: 5, c: 8, d: 13, e: 8]", $result);
	}

	public function testMapRecord(): void {
		$result = $this->executeCodeSnippet(
			"doMap[a: 1, b: 2, c: 5, d: 10, e: 5];",
			valueDeclarations: "
				doMap = ^m: Result<[a: Integer, b: Integer, ... Integer], Null> => Result<Map<Integer>, Null> ::
					m->map(^i: Integer => Integer :: i + 3);
			"
		);
		$this->assertEquals("[a: 4, b: 5, c: 8, d: 13, e: 8]", $result);
	}

	public function testMapMapNonEmptyCallbackError(): void {
		$result = $this->executeCodeSnippet(
			"doMap[a: 1, b: 2, c: 5, d: 10, e: 5];",
			valueDeclarations: "
				doMap = ^m: Result<Map<Integer>, Null> => Result<Map<Integer>, Null|String> ::
					m->map(^Integer => Result<Integer, String> :: @'error');
			"
		);
		$this->assertEquals("@'error'", $result);
	}

	public function testMapMapWithResultError(): void {
		$result = $this->executeCodeSnippet(
			"doMap(@'initial_error');",
			valueDeclarations: "
				doMap = ^m: Result<Map<Integer>, String> => Result<Map<Integer>, String> ::
					m->map(^i: Integer => Integer :: i + 3);
			"
		);
		$this->assertEquals("@'initial_error'", $result);
	}

	public function testMapMapWithResultErrorAndCallbackError(): void {
		$result = $this->executeCodeSnippet(
			"doMap(@'first_error');",
			valueDeclarations: "
				doMap = ^m: Result<Map<Integer>, String> => Result<Map<Integer>, String|Integer> ::
					m->map(^Integer => Result<Integer, Integer> :: @999);
			"
		);
		$this->assertEquals("@'first_error'", $result);
	}

	public function testMapMapWithNullError(): void {
		$result = $this->executeCodeSnippet(
			"doMap[a: 1, b: 2];",
			valueDeclarations: "
				doMap = ^m: Result<Map<Integer>, Null> => Result<Map<Integer>, Null> ::
					m->map(^i: Integer => Integer :: i + 3);
			"
		);
		$this->assertEquals("[a: 4, b: 5]", $result);
	}

	public function testMapMapResultErrorNull(): void {
		$result = $this->executeCodeSnippet(
			"doMap(@null);",
			valueDeclarations: "
				doMap = ^m: Result<Map<Integer>, Null> => Result<Map<Integer>, Null> ::
					m->map(^i: Integer => Integer :: i + 3);
			"
		);
		$this->assertEquals("@null", $result);
	}

	public function testMapMapKeyType(): void {
		$result = $this->executeCodeSnippet(
			"doMap[a: 1, b: 2];",
			valueDeclarations: "
				doMap = ^m: Result<Map<String<1>:Integer>, Null> => Result<Map<String<1>:Integer>, Null> ::
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
				doSet = ^s: Result<Set<Integer>, Null> => Result<Set<Integer>, Null> ::
					s->map(^i: Integer => Integer :: i + 3);
			"
		);
		$this->assertEquals("[;]", $result);
	}

	public function testMapSetNonEmpty(): void {
		$result = $this->executeCodeSnippet(
			"doSet[1; 2; 5; 10; 5];",
			valueDeclarations: "
				doSet = ^s: Result<Set<Integer>, Null> => Result<Set<Integer>, Null> ::
					s->map(^i: Integer => Integer :: i + 3);
			"
		);
		$this->assertEquals("[4; 5; 8; 13]", $result);
	}

	public function testMapSetNonUnique(): void {
		$result = $this->executeCodeSnippet(
			"doSet['hello'; 'world'; 'hi'];",
			valueDeclarations: "
				doSet = ^s: Result<Set<String>, Null> => Result<Set<Integer>, Null> ::
					s->map(^st: String => Integer :: st->length);
			"
		);
		$this->assertEquals("[5; 2]", $result);
	}

	public function testMapSetNonEmptyCallbackError(): void {
		$result = $this->executeCodeSnippet(
			"doSet[1; 2; 5; 10; 5];",
			valueDeclarations: "
				doSet = ^s: Result<Set<Integer>, Null> => Result<Set<Integer>, Null|String> ::
					s->map(^Integer => Result<Integer, String> :: @'error');
			"
		);
		$this->assertEquals("@'error'", $result);
	}

	public function testMapSetWithResultError(): void {
		$result = $this->executeCodeSnippet(
			"doSet(@'initial_error');",
			valueDeclarations: "
				doSet = ^s: Result<Set<Integer>, String> => Result<Set<Integer>, String> ::
					s->map(^i: Integer => Integer :: i + 3);
			"
		);
		$this->assertEquals("@'initial_error'", $result);
	}

	public function testMapSetWithResultErrorAndCallbackError(): void {
		$result = $this->executeCodeSnippet(
			"doSet(@'first_error');",
			valueDeclarations: "
				doSet = ^s: Result<Set<Integer>, String> => Result<Set<Integer>, String|Integer> ::
					s->map(^Integer => Result<Integer, Integer> :: @999);
			"
		);
		$this->assertEquals("@'first_error'", $result);
	}

	public function testMapSetWithNullError(): void {
		$result = $this->executeCodeSnippet(
			"doSet[1; 2];",
			valueDeclarations: "
				doSet = ^s: Result<Set<Integer>, Null> => Result<Set<Integer>, Null> ::
					s->map(^i: Integer => Integer :: i + 3);
			"
		);
		$this->assertEquals("[4; 5]", $result);
	}

	public function testMapSetResultErrorNull(): void {
		$result = $this->executeCodeSnippet(
			"doSet(@null);",
			valueDeclarations: "
				doSet = ^s: Result<Set<Integer>, Null> => Result<Set<Integer>, Null> ::
					s->map(^i: Integer => Integer :: i + 3);
			"
		);
		$this->assertEquals("@null", $result);
	}

	// Error cases

	public function testMapArrayInvalidReturnType(): void {
		$this->executeErrorCodeSnippet(
			"Expected a return value of type Array<Integer>, got Result<Array<Integer>, Null>",
			"doMap([]);",
			valueDeclarations: "
				doMap = ^a: Result<Array<Integer>, Null> => Array<Integer> ::
					a->map(^i: Integer => Integer :: i + 3);
			"
		);
	}

	public function testMapMapInvalidReturnType(): void {
		$this->executeErrorCodeSnippet(
			"Expected a return value of type Map<Integer>, got Result<Map<Integer>, Null>",
			"doMap([:]);",
			valueDeclarations: "
				doMap = ^m: Result<Map<Integer>, Null> => Map<Integer> ::
					m->map(^i: Integer => Integer :: i + 3);
			"
		);
	}

	public function testMapArrayInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type',
			"doMap([]);",
			valueDeclarations: "
				doMap = ^a: Result<Array<Integer>, Null> => Result<Array<Integer>, Null> ::
					a->map(5);
			"
		);
	}

	public function testMapMapInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type',
			"doMap([:]);",
			valueDeclarations: "
				doMap = ^m: Result<Map<Integer>, Null> => Result<Map<Integer>, Null> ::
					m->map(5);
			"
		);
	}

	public function testMapArrayInvalidParameterParameterType(): void {
		$this->executeErrorCodeSnippet("The parameter type Integer of the callback function is not a subtype of Boolean",
			"doMap([1, 2, 5, 10]);",
			valueDeclarations: "
				doMap = ^a: Result<Array<Integer>, Null> => Result<Array<Integer>, Null> ::
					a->map(^Boolean => Boolean :: true);
			"
		);
	}

	public function testMapMapInvalidParameterParameterType(): void {
		$this->executeErrorCodeSnippet("The parameter type Boolean of the callback function is not a supertype of Integer",
			"doMap([a: 1, b: 2]);",
			valueDeclarations: "
				doMap = ^m: Result<Map<Integer>, Null> => Result<Map<Integer>, Null> ::
					m->map(^Boolean => Boolean :: true);
			"
		);
	}

	public function testMapSetInvalidReturnType(): void {
		$this->executeErrorCodeSnippet(
			"Expected a return value of type Set<Integer>, got Result<Set<Integer>, Null>",
			"doSet([;]);",
			valueDeclarations: "
				doSet = ^s: Result<Set<Integer>, Null> => Set<Integer> ::
					s->map(^i: Integer => Integer :: i + 3);
			"
		);
	}

	public function testMapSetInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type',
			"doSet([;]);",
			valueDeclarations: "
				doSet = ^s: Result<Set<Integer>, Null> => Result<Set<Integer>, Null> ::
					s->map(5);
			"
		);
	}

	public function testMapSetInvalidParameterParameterType(): void {
		$this->executeErrorCodeSnippet("The parameter type Integer of the callback function is not a subtype of Boolean",
			"doSet[1; 2];",
			valueDeclarations: "
				doSet = ^s: Result<Set<Integer>, Null> => Result<Set<Integer>, Null> ::
					s->map(^Boolean => Boolean :: true);
			"
		);
	}

	// Union
	/* disabled for now:
	public function testMapUnion(): void {
		$result = $this->executeCodeSnippet(
			"doMap[a: 1, b: 2, c: 5, d: 10, e: 5];",
			valueDeclarations: "
				doMap = ^m: Result<Array<Integer>|Map<String<1>|Integer>|Set<Integer>, Null> => Result<Array<Integer>|Map<String<1>|Integer>|Set<Integer>, Null> ::
					m->map(^i: Integer => Integer :: i + 3);
			"
		);
		$this->assertEquals("[a: 4, b: 5, c: 8, d: 13, e: 8]", $result);
	}
	*/
}
