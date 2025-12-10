<?php

namespace Walnut\Lang\Test\NativeCode\Map;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class WithoutByKeyTest extends CodeExecutionTestHelper {

	public function testWithoutByKeyEmpty(): void {
		$result = $this->executeCodeSnippet("[:]->withoutByKey('r');");
		$this->assertEquals("@MapItemNotFound![key: 'r']", $result);
	}

	public function testWithoutByKeyNotFound(): void {
		$result = $this->executeCodeSnippet("[a: 1, b: 2, c: 5, d: 10, e: 5]->withoutByKey('r');");
		$this->assertEquals("@MapItemNotFound![key: 'r']", $result);
	}

	public function testWithoutByKeyNonEmpty(): void {
		$result = $this->executeCodeSnippet("[a: 1, b: 2, c: 5]->withoutByKey('b');");
		$this->assertEquals("[element: 2, map: [a: 1, c: 5]]", $result);
	}

	public function testWithoutByKeyType(): void {
		$result = $this->executeCodeSnippet(
			"fn[a: 1, b: 2];",
			valueDeclarations: "fn = ^m: Map<String<1>:Integer> => Result<[element: Integer, map: Map<String<1>:Integer>], MapItemNotFound> :: 
				m->withoutByKey('a');"
		);
		$this->assertEquals("[element: 1, map: [b: 2]]", $result);
	}

	public function testWithoutByKeyTypeStringSubset(): void {
		$result = $this->executeCodeSnippet(
			"fn[a: 1, b: 2];",
			valueDeclarations: "fn = ^m: Map<String['a', 'b']:Integer> => Result<[element: Integer, map: Map<String['b']:Integer>], MapItemNotFound> :: 
				m->withoutByKey('a');"
		);
		$this->assertEquals("[element: 1, map: [b: 2]]", $result);
	}

	public function testFilterRecordSingleValue(): void {
		$result = $this->executeCodeSnippet(
			"fn[map: [a: 1.72, b: 2, c: 3, d: 'hello', e: 'hi!'], key: 'c'];",
			valueDeclarations: "fn = ^m: [map: [a: Real, b: ?Integer, c: Real|Boolean, ...String], key: String['c']]
				=> [element: Real|Boolean, map: [a: Real, b: ?Integer, ...String]] :: 
					m.map->withoutByKey(m.key);"
		);
		$this->assertEquals("[\n	element: 3,\n	map: [a: 1.72, b: 2, d: 'hello', e: 'hi!']\n]", $result);
	}

	public function testFilterRecordMultipleValues(): void {
		$result = $this->executeCodeSnippet(
			"fn[map: [a: 1.72, b: 2, c: 3, d: 'hello', e: 'hi!'], key: 'c'];",
			valueDeclarations: "fn = ^m: [map: [a: Real, b: ?Integer, c: Real|Boolean, ...String], key: String['a', 'c']]
				=> [element: Real|Boolean, map: [a: ?Real, b: ?Integer, c: ?Real|Boolean, ...String]] :: 
					m.map->withoutByKey(m.key);"
		);
		$this->assertEquals("[\n	element: 3,\n	map: [a: 1.72, b: 2, d: 'hello', e: 'hi!']\n]", $result);
	}

	public function testFilterRecordMultipleValuesNotFound(): void {
		$result = $this->executeCodeSnippet(
			"fn[map: [a: 1.72, b: 2, c: 3, d: 'hello', e: 'hi!'], key: 'c'];",
			valueDeclarations: "fn = ^m: [map: [a: Real, b: ?Integer, c: Real|Boolean, ...String], key: String['a', 'c', 'x']]
				=> Result<[element: Real|Boolean|String, map: [a: ?Real, b: ?Integer, c: ?Real|Boolean, ...String]], MapItemNotFound> :: 
					m.map->withoutByKey(m.key);"
		);
		$this->assertEquals("[\n	element: 3,\n	map: [a: 1.72, b: 2, d: 'hello', e: 'hi!']\n]", $result);
	}

	public function testWithoutByKeyInvalidParameterValue(): void {
		$this->executeErrorCodeSnippet("Invalid parameter type",
			"[a: 'a', b: 1, c: 2]->withoutByKey(15)");
	}
}