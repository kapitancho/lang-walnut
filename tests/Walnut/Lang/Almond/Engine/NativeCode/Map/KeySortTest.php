<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Map;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class KeySortTest extends CodeExecutionTestHelper {

	public function testSortEmpty(): void {
		$result = $this->executeCodeSnippet("[:]->keySort;");
		$this->assertEquals("[:]", $result);
	}

	public function testSortNonEmptyStrings(): void {
		$result = $this->executeCodeSnippet("[c: 'hello', a: 'world', b: 'hi', d: 'hello']->keySort;");
		$this->assertEquals("[\n	a: 'world',\n	b: 'hi',\n	c: 'hello',\n	d: 'hello'\n]", $result);
	}

	public function testSortNonEmptyStringsReverseRegular(): void {
		$result = $this->executeCodeSnippet("[c: 'hello', a: 'world', b: 'hi', d: 'hello']->keySort[reverse: false];");
		$this->assertEquals("[\n	a: 'world',\n	b: 'hi',\n	c: 'hello',\n	d: 'hello'\n]", $result);
	}

	public function testSortNonEmptyStringsReverse(): void {
		$result = $this->executeCodeSnippet("[c: 'hello', a: 'world', b: 'hi', d: 'hello']->keySort[reverse: true];");
		$this->assertEquals("[\n	d: 'hello',\n	c: 'hello',\n	b: 'hi',\n	a: 'world'\n]", $result);
	}

	public function testSortInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('The parameter type Integer[1] is not a subtype of (Null|[reverse: Boolean])', "[a: 1, b: 2, c: 5.3, d: 2]->keySort(1);");
	}

	public function testSortInvalidParameterTypeRecordKeys(): void {
		$this->executeErrorCodeSnippet('The parameter type [reverse: True, x: Integer[7]] is not a subtype of (Null|[reverse: Boolean])', "[a: 1, b: 2, c: 5.3, d: 2]->keySort[reverse: true, x: 7];");
	}

	public function testSortInvalidParameterTypeRecordValue(): void {
		$this->executeErrorCodeSnippet('The parameter type [reverse: Integer[4]] is not a subtype of (Null|[reverse: Boolean])', "[a: 1, b: 2, c: 5.3, d: 2]->keySort[reverse: 4];");
	}
}