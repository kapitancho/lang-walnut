<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Map;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class SortTest extends CodeExecutionTestHelper {

	public function testSortEmpty(): void {
		$result = $this->executeCodeSnippet("[:]->sort;");
		$this->assertEquals("[:]", $result);
	}

	public function testSortNonEmptyNumbers(): void {
		$result = $this->executeCodeSnippet("[a: 1, b: 21, c: 5.3, d: 2]->sort;");
		$this->assertEquals("[a: 1, d: 2, c: 5.3, b: 21]", $result);
	}

	public function testSortNonEmptyNumbersReverseRegular(): void {
		$result = $this->executeCodeSnippet("[a: 1, b: 21, c: 5.3, d: 2]->sort[reverse: false];");
		$this->assertEquals("[a: 1, d: 2, c: 5.3, b: 21]", $result);
	}

	public function testSortNonEmptyNumbersReverse(): void {
		$result = $this->executeCodeSnippet("[a: 1, b: 21, c: 5.3, d: 2]->sort[reverse: true];");
		$this->assertEquals("[b: 21, c: 5.3, d: 2, a: 1]", $result);
	}

	public function testSortNonEmptyStrings(): void {
		$result = $this->executeCodeSnippet("[a: 'hello', b: 'world', c: 'hi']->sort;");
		$this->assertEquals("[a: 'hello', c: 'hi', b: 'world']", $result);
	}

	public function testSortNonEmptyStringsReverseRegular(): void {
		$result = $this->executeCodeSnippet("[a: 'hello', b: 'world', c: 'hi']->sort[reverse: false];");
		$this->assertEquals("[a: 'hello', c: 'hi', b: 'world']", $result);
	}

	public function testSortNonEmptyStringsReverse(): void {
		$result = $this->executeCodeSnippet("[a: 'hello', b: 'world', c: 'hi']->sort[reverse: true];");
		$this->assertEquals("[b: 'world', c: 'hi', a: 'hello']", $result);
	}

	public function testSortInvalidTargetType(): void {
		$this->executeErrorCodeSnippet('Invalid target type', "[a: 12, b: 'hello', c: 'world', d: 'hi', e: 'hello']->sort;");
	}

	public function testSortInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('The parameter type Integer[1] is not a subtype of (Null|[reverse: Boolean])', "[a: 1, b: 2, c: 5.3, d: 2]->sort(1);");
	}

	public function testSortInvalidParameterTypeRecordKeys(): void {
		$this->executeErrorCodeSnippet('The parameter type [reverse: True, x: Integer[7]] is not a subtype of (Null|[reverse: Boolean])', "[a: 1, b: 2, c: 5.3, d: 2]->sort[reverse: true, x: 7];");
	}

	public function testSortInvalidParameterTypeRecordValue(): void {
		$this->executeErrorCodeSnippet('The parameter type [reverse: Integer[4]] is not a subtype of (Null|[reverse: Boolean])', "[a: 1, b: 2, c: 5.3, d: 2]->sort[reverse: 4];");
	}
}