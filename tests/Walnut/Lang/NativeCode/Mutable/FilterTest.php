<?php

namespace Walnut\Lang\Test\NativeCode\Mutable;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class FilterTest extends CodeExecutionTestHelper {

	// Array filter tests
	public function testFilterArrayEmpty(): void {
		$result = $this->executeCodeSnippet("mutable{Array, []}->FILTER(^Any => True :: true);");
		$this->assertEquals("mutable{Array, []}", $result);
	}

	public function testFilterArrayNonEmpty(): void {
		$result = $this->executeCodeSnippet(
			"testFn(mutable{Array<Integer>, [1, 2, 5, 10, 5]});",
			valueDeclarations: "testFn = ^m: Mutable<Array<Integer>> => Mutable<Array<Integer>> :: m->FILTER(^i: Integer => Boolean :: i > 4);"
		);
		$this->assertEquals("mutable{Array<Integer>, [5, 10, 5]}", $result);
	}

	public function testFilterArrayAllFiltered(): void {
		$result = $this->executeCodeSnippet(
			"testFn(mutable{Array<Integer>, [1, 2, 3]});",
			valueDeclarations: "testFn = ^m: Mutable<Array<Integer>> => Mutable<Array<Integer>> :: m->FILTER(^i: Integer => Boolean :: i > 10);"
		);
		$this->assertEquals("mutable{Array<Integer>, []}", $result);
	}

	public function testFilterArrayNoneFiltered(): void {
		$result = $this->executeCodeSnippet(
			"testFn(mutable{Array<Integer>, [5, 10, 15]});",
			valueDeclarations: "testFn = ^m: Mutable<Array<Integer>> => Mutable<Array<Integer>> :: m->FILTER(^i: Integer => Boolean :: i > 0);"
		);
		$this->assertEquals("mutable{Array<Integer>, [5, 10, 15]}", $result);
	}

	public function testFilterArrayWithStrings(): void {
		$result = $this->executeCodeSnippet(
			"testFn(mutable{Array<String>, ['hello', 'world', 'hi']});",
			valueDeclarations: "testFn = ^m: Mutable<Array<String>> => Mutable<Array<String>> :: m->FILTER(^s: String => Boolean :: s->length > 3);"
		);
		$this->assertEquals("mutable{Array<String>, ['hello', 'world']}", $result);
	}

	public function testFilterArrayInvalidParameterRange(): void {
		$result = $this->executeErrorCodeSnippet(
			"Invalid target type: Mutable<Array<Integer, 3..>>",
			"testFn(mutable{Array<Integer, 3..>, [1, 2, 5, 10, 5]});",
			valueDeclarations: "testFn = ^m: Mutable<Array<Integer, 3..>> => Mutable<Array<Integer, 3..>> :: m->FILTER(^i: Integer => Boolean :: i > 4);"
		);
	}

	// Map filter tests
	public function testFilterMapEmpty(): void {
		$result = $this->executeCodeSnippet("mutable{Map, [:]}->FILTER(^Any => Boolean :: true);");
		$this->assertEquals("mutable{Map, [:]}", $result);
	}

	public function testFilterMapNonEmpty(): void {
		$result = $this->executeCodeSnippet(
			"testFn(mutable{Map<Integer>, [a: 1, b: 2, c: 5, d: 10, e: 5]});",
			valueDeclarations: "testFn = ^m: Mutable<Map<Integer>> => Mutable<Map<Integer>> :: m->FILTER(^i: Integer => Boolean :: i > 4);"
		);
		$this->assertEquals("mutable{Map<Integer>, [c: 5, d: 10, e: 5]}", $result);
	}

	public function testFilterMapAllFiltered(): void {
		$result = $this->executeCodeSnippet(
			"testFn(mutable{Map<Integer>, [a: 1, b: 2, c: 3]});",
			valueDeclarations: "testFn = ^m: Mutable<Map<Integer>> => Mutable<Map<Integer>> :: m->FILTER(^i: Integer => Boolean :: i > 10);"
		);
		$this->assertEquals("mutable{Map<Integer>, [:]}", $result);
	}

	public function testFilterMapNoneFiltered(): void {
		$result = $this->executeCodeSnippet(
			"testFn(mutable{Map<Integer>, [a: 5, b: 10, c: 15]});",
			valueDeclarations: "testFn = ^m: Mutable<Map<Integer>> => Mutable<Map<Integer>> :: m->FILTER(^i: Integer => Boolean :: i > 0);"
		);
		$this->assertEquals("mutable{Map<Integer>, [a: 5, b: 10, c: 15]}", $result);
	}

	public function testFilterMapKeyType(): void {
		$result = $this->executeCodeSnippet(
			"fn(mutable{Map<String<1>:Integer>, [a: 1, b: 2]});",
			valueDeclarations: "fn = ^m: Mutable<Map<String<1>:Integer>> => Mutable<Map<String<1>:Integer>> ::
				m->FILTER(^v: Integer => Boolean :: v > 1);"
		);
		$this->assertEquals("mutable{Map<String<1>:Integer>, [b: 2]}", $result);
	}

	// Set filter tests
	public function testFilterSetEmpty(): void {
		$result = $this->executeCodeSnippet("mutable{Set, [;]}->FILTER(^Any => Boolean :: true);");
		$this->assertEquals("mutable{Set, [;]}", $result);
	}

	public function testFilterSetNonEmpty(): void {
		$result = $this->executeCodeSnippet(
			"testFn(mutable{Set<Integer>, [1; 2; 5; 10; 5]});",
			valueDeclarations: "testFn = ^m: Mutable<Set<Integer>> => Mutable<Set<Integer>> :: m->FILTER(^i: Integer => Boolean :: i > 4);"
		);
		$this->assertEquals("mutable{Set<Integer>, [5; 10]}", $result);
	}

	public function testFilterSetAllFiltered(): void {
		$result = $this->executeCodeSnippet(
			"testFn(mutable{Set<Integer>, [1; 2; 3]});",
			valueDeclarations: "testFn = ^m: Mutable<Set<Integer>> => Mutable<Set<Integer>> :: m->FILTER(^i: Integer => Boolean :: i > 10);"
		);
		$this->assertEquals("mutable{Set<Integer>, [;]}", $result);
	}

	public function testFilterSetNoneFiltered(): void {
		$result = $this->executeCodeSnippet(
			"testFn(mutable{Set<Integer>, [5; 10; 15]});",
			valueDeclarations: "testFn = ^m: Mutable<Set<Integer>> => Mutable<Set<Integer>> :: m->FILTER(^i: Integer => Boolean :: i > 0);"
		);
		$this->assertEquals("mutable{Set<Integer>, [5; 10; 15]}", $result);
	}

	public function testFilterSetWithStrings(): void {
		$result = $this->executeCodeSnippet(
			"testFn(mutable{Set<String>, ['hello'; 'world'; 'hi']});",
			valueDeclarations: "testFn = ^m: Mutable<Set<String>> => Mutable<Set<String>> :: m->FILTER(^s: String => Boolean :: s->length > 3);"
		);
		$this->assertEquals("mutable{Set<String>, ['hello'; 'world']}", $result);
	}

	// Error cases
	public function testFilterArrayInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type',
			"testFn(mutable{Array<Integer>, [1]});",
			valueDeclarations: "testFn = ^m: Mutable<Array<Integer>> => Mutable<Array<Integer>> :: m->FILTER(5);"
		);
	}

	public function testFilterArrayInvalidParameterParameterType(): void {
		$this->executeErrorCodeSnippet("The parameter type Integer of the callback function is not a subtype of Boolean",
			"testFn(mutable{Array<Integer>, [1]});",
			valueDeclarations: "testFn = ^m: Mutable<Array<Integer>> => Mutable<Array<Integer>> :: m->FILTER(^Boolean => Boolean :: true);"
		);
	}

	public function testFilterArrayInvalidParameterReturnType(): void {
		$this->executeErrorCodeSnippet("Invalid parameter type",
			"testFn(mutable{Array<Integer>, [1]});",
			valueDeclarations: "testFn = ^m: Mutable<Array<Integer>> => Mutable<Array<Integer>> :: m->FILTER(^Integer => Real :: 3.14);"
		);
	}

	public function testFilterMapInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type',
			"testFn(mutable{Map<Integer>, [a: 1]});",
			valueDeclarations: "testFn = ^m: Mutable<Map<Integer>> => Mutable<Map<Integer>> :: m->FILTER(5);"
		);
	}

	public function testFilterMapInvalidParameterParameterType(): void {
		$this->executeErrorCodeSnippet("The parameter type Integer of the callback function is not a subtype of Boolean",
			"testFn(mutable{Map<Integer>, [a: 1]});",
			valueDeclarations: "testFn = ^m: Mutable<Map<Integer>> => Mutable<Map<Integer>> :: m->FILTER(^Boolean => Boolean :: true);"
		);
	}

	public function testFilterMapInvalidParameterReturnType(): void {
		$this->executeErrorCodeSnippet("Invalid parameter type",
			"testFn(mutable{Map<Integer>, [a: 1]});",
			valueDeclarations: "testFn = ^m: Mutable<Map<Integer>> => Mutable<Map<Integer>> :: m->FILTER(^Integer => Real :: 3.14);"
		);
	}

	public function testFilterSetInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type',
			"testFn(mutable{Set<Integer>, [1;]});",
			valueDeclarations: "testFn = ^m: Mutable<Set<Integer>> => Mutable<Set<Integer>> :: m->FILTER(5);"
		);
	}

	public function testFilterSetInvalidParameterParameterType(): void {
		$this->executeErrorCodeSnippet("The parameter type Integer of the callback function is not a subtype of Boolean",
			"testFn(mutable{Set<Integer>, [1;]});",
			valueDeclarations: "testFn = ^m: Mutable<Set<Integer>> => Mutable<Set<Integer>> :: m->FILTER(^Boolean => Boolean :: true);"
		);
	}

	public function testFilterSetInvalidParameterReturnType(): void {
		$this->executeErrorCodeSnippet("Invalid parameter type",
			"testFn(mutable{Set<Integer>, [1;]});",
			valueDeclarations: "testFn = ^m: Mutable<Set<Integer>> => Mutable<Set<Integer>> :: m->FILTER(^Integer => Real :: 3.14);"
		);
	}

}
