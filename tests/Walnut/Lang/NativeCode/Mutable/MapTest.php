<?php

namespace Walnut\Lang\Test\NativeCode\Mutable;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class MapTest extends CodeExecutionTestHelper {

	// Array map tests
	public function testMapArrayEmpty(): void {
		$result = $this->executeCodeSnippet("mutable{Array, []}->MAP(^a: Any => Any :: a);");
		$this->assertEquals("mutable{Array, []}", $result);
	}

	public function testMapArrayNonEmpty(): void {
		$result = $this->executeCodeSnippet(
			"testFn(mutable{Array<Integer>, [1, 2, 5, 10, 5]});",
			valueDeclarations: "testFn = ^m: Mutable<Array<Integer>> => Mutable<Array<Integer>> :: m->MAP(^i: Integer => Integer :: i + 3);"
		);
		$this->assertEquals("mutable{Array<Integer>, [4, 5, 8, 13, 8]}", $result);
	}

	public function testMapArrayMultiply(): void {
		$result = $this->executeCodeSnippet(
			"testFn(mutable{Array<Integer>, [1, 2, 3, 4]});",
			valueDeclarations: "testFn = ^m: Mutable<Array<Integer>> => Mutable<Array<Integer>> :: m->MAP(^i: Integer => Integer :: i * 2);"
		);
		$this->assertEquals("mutable{Array<Integer>, [2, 4, 6, 8]}", $result);
	}

	// Map map tests
	public function testMapMapEmpty(): void {
		$result = $this->executeCodeSnippet("mutable{Map, [:]}->MAP(^a: Any => Any :: a);");
		$this->assertEquals("mutable{Map, [:]}", $result);
	}

	public function testMapMapNonEmpty(): void {
		$result = $this->executeCodeSnippet(
			"testFn(mutable{Map<Integer, 2..>, [a: 1, b: 2, c: 5, d: 10, e: 5]});",
			valueDeclarations: "testFn = ^m: Mutable<Map<Integer, 2..>> => Mutable<Map<Integer, 2..>> :: m->MAP(^i: Integer => Integer :: i + 3);"
		);
		$this->assertEquals("mutable{Map<Integer, 2..>, [a: 4, b: 5, c: 8, d: 13, e: 8]}", $result);
	}

	public function testMapMapKeyType(): void {
		$result = $this->executeCodeSnippet(
			"fn(mutable{Map<String<1>:Integer>, [a: 1, b: 2]});",
			valueDeclarations: "fn = ^m: Mutable<Map<String<1>:Integer>> => Mutable<Map<String<1>:Integer>> ::
				m->MAP(^v: Integer => Integer :: v * 10);"
		);
		$this->assertEquals("mutable{Map<String<1>:Integer>, [a: 10, b: 20]}", $result);
	}

	// Set map tests
	public function testMapSetEmpty(): void {
		$result = $this->executeCodeSnippet("mutable{Set, [;]}->MAP(^a: Any => Any :: a);");
		$this->assertEquals("mutable{Set, [;]}", $result);
	}

	public function testMapSetNonEmpty(): void {
		$result = $this->executeCodeSnippet(
			"testFn(mutable{Set<Integer>, [1; 2; 5; 10; 5]});",
			valueDeclarations: "testFn = ^m: Mutable<Set<Integer>> => Mutable<Set<Integer>> :: m->MAP(^i: Integer => Integer :: i + 3);"
		);
		$this->assertEquals("mutable{Set<Integer>, [4; 5; 8; 13]}", $result);
	}

	public function testMapSetWithCollisions(): void {
		$result = $this->executeCodeSnippet(
			"testFn(mutable{Set<Integer, 1..>, [1; 2; 3; 4]});",
			valueDeclarations: "testFn = ^m: Mutable<Set<Integer, 1..>> => Mutable<Set<Integer, 1..>> :: m->MAP(^i: Integer => Integer :: i % 2);"
		);
		$this->assertEquals("mutable{Set<Integer, 1..>, [1; 0]}", $result);
	}

	// Error cases
	public function testMapArrayInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type',
			"testFn(mutable{Array<Integer>, [1]});",
			valueDeclarations: "testFn = ^m: Mutable<Array<Integer>> => Mutable<Array<Integer>> :: m->MAP(5);"
		);
	}

	public function testMapArrayInvalidParameterReturnType(): void {
		$this->executeErrorCodeSnippet("The value type Real is not a subtype of the return type Integer of the callback function",
			"testFn(mutable{Array<Integer>, [1]});",
			valueDeclarations: "testFn = ^m: Mutable<Array<Integer>> => Mutable<Array<Integer>> :: m->MAP(^b: Integer => Real :: 1);"
		);
	}

	public function testMapArrayInvalidParameterParameterType(): void {
		$this->executeErrorCodeSnippet("The parameter type Integer of the callback function is not a subtype of Boolean",
			"testFn(mutable{Array<Integer>, [1]});",
			valueDeclarations: "testFn = ^m: Mutable<Array<Integer>> => Mutable<Array<Integer>> :: m->MAP(^b: Boolean => Integer :: 1);"
		);
	}

	public function testMapMapInvalidTargetTypeSet(): void {
		$this->executeErrorCodeSnippet('Invalid target type',
			"testFn(mutable{Set<Integer, 2..>, [1; 2; 3; 4]});",
			valueDeclarations: "testFn = ^m: Mutable<Set<Integer, 2..>> => Mutable<Set<Integer, 2..>> :: m->MAP(^i: Integer => Integer :: i % 2);"
		);
	}

	public function testMapMapInvalidTargetTypeOther(): void {
		$this->executeErrorCodeSnippet('Invalid target type',
			"testFn(mutable{Boolean, false});",
			valueDeclarations: "testFn = ^m: Mutable<Boolean> => Mutable<Boolean> :: m->MAP(^i: Integer => Integer :: i % 2);"
		);
	}

	public function testMapMapInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type',
			"testFn(mutable{Map<Integer>, [a: 1]});",
			valueDeclarations: "testFn = ^m: Mutable<Map<Integer>> => Mutable<Map<Integer>> :: m->MAP(5);"
		);
	}

	public function testMapMapInvalidParameterParameterType(): void {
		$this->executeErrorCodeSnippet("The parameter type Integer of the callback function is not a subtype of Boolean",
			"testFn(mutable{Map<Integer>, [a: 1]});",
			valueDeclarations: "testFn = ^m: Mutable<Map<Integer>> => Mutable<Map<Integer>> :: m->MAP(^b: Boolean => Integer :: 1);"
		);
	}

	public function testMapSetInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type',
			"testFn(mutable{Set<Integer>, [1;]});",
			valueDeclarations: "testFn = ^m: Mutable<Set<Integer>> => Mutable<Set<Integer>> :: m->MAP(5);"
		);
	}

	public function testMapSetInvalidParameterParameterType(): void {
		$this->executeErrorCodeSnippet("The parameter type Integer of the callback function is not a subtype of Boolean",
			"testFn(mutable{Set<Integer>, [1;]});",
			valueDeclarations: "testFn = ^m: Mutable<Set<Integer>> => Mutable<Set<Integer>> :: m->MAP(^b: Boolean => Integer :: 1);"
		);
	}

}
