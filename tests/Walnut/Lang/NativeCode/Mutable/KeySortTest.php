<?php

namespace Walnut\Lang\NativeCode\Mutable;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class KeySortTest extends CodeExecutionTestHelper {

	public function testSortEmpty(): void {
		$result = $this->executeCodeSnippet("mutable{Map, [:]}->KEYSORT;");
		$this->assertEquals("mutable{Map, [:]}", $result);
	}

	public function testSortNonEmptyStrings(): void {
		$result = $this->executeCodeSnippet("mutable{Map, [c: 'hello', a: 'world', b: 'hi', d: 'hello']}->KEYSORT;");
		$this->assertEquals("mutable{Map, [\n	a: 'world',\n	b: 'hi',\n	c: 'hello',\n	d: 'hello'\n]}", $result);
	}

	public function testSortNonEmptyStringsReverseRegular(): void {
		$result = $this->executeCodeSnippet("mutable{Map, [c: 'hello', a: 'world', b: 'hi', d: 'hello']}->KEYSORT[reverse: false];");
		$this->assertEquals("mutable{Map, [\n	a: 'world',\n	b: 'hi',\n	c: 'hello',\n	d: 'hello'\n]}", $result);
	}

	public function testSortNonEmptyStringsReverse(): void {
		$result = $this->executeCodeSnippet("mutable{Map, [c: 'hello', a: 'world', b: 'hi', d: 'hello']}->KEYSORT[reverse: true];");
		$this->assertEquals("mutable{Map, [\n	d: 'hello',\n	c: 'hello',\n	b: 'hi',\n	a: 'world'\n]}", $result);
	}

	public function testSortInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('The parameter type Integer[1] is not a subtype of (Null|[reverse: Boolean])', "mutable{Map, [a: 1, b: 2, c: 5.3, d: 2]}->KEYSORT(1);");
	}

	public function testSortInvalidParameterTypeRecordKeys(): void {
		$this->executeErrorCodeSnippet('The parameter type [reverse: True, x: Integer[7]] is not a subtype of (Null|[reverse: Boolean])', "mutable{Map<Real>, [a: 1, b: 2, c: 5.3, d: 2]}->KEYSORT[reverse: true, x: 7];");
	}

	public function testSortInvalidParameterTypeRecordValue(): void {
		$this->executeErrorCodeSnippet('The parameter type [reverse: Integer[4]] is not a subtype of (Null|[reverse: Boolean])', "mutable{Map<Real>, [a: 1, b: 2, c: 5.3, d: 2]}->KEYSORT[reverse: 4];");
	}
}