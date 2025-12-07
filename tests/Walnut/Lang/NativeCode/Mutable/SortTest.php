<?php

namespace Walnut\Lang\NativeCode\Mutable;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class SortTest extends CodeExecutionTestHelper {

	public function testSortEmpty(): void {
		$result = $this->executeCodeSnippet("mutable{Array<Integer>, []}->SORT;");
		$this->assertEquals("mutable{Array<Integer>, []}", $result);
	}

	public function testSortNonEmptyNumbers(): void {
		$result = $this->executeCodeSnippet("mutable{Array<Real>, [1, 2, 5.3, 2]}->SORT;");
		$this->assertEquals("mutable{Array<Real>, [1, 2, 2, 5.3]}", $result);
	}

	public function testSortNonEmptyNumbersReverseRegular(): void {
		$result = $this->executeCodeSnippet("mutable{Array<Real>, [1, 2, 5.3, 2]}->SORT[reverse: false];");
		$this->assertEquals("mutable{Array<Real>, [1, 2, 2, 5.3]}", $result);
	}

	public function testSortNonEmptyNumbersReverse(): void {
		$result = $this->executeCodeSnippet("mutable{Array<Real>, [1, 2, 5.3, 2]}->SORT[reverse: true];");
		$this->assertEquals("mutable{Array<Real>, [5.3, 2, 2, 1]}", $result);
	}

	public function testSortNonEmptyStrings(): void {
		$result = $this->executeCodeSnippet("mutable{Array<String>, ['hello','world', 'hi', 'hello']}->SORT;");
		$this->assertEquals("mutable{Array<String>, ['hello', 'hello', 'hi', 'world']}", $result);
	}

	public function testSortNonEmptyStringsReverseRegular(): void {
		$result = $this->executeCodeSnippet("mutable{Array<String>, ['hello','world', 'hi', 'hello']}->SORT[reverse: false];");
		$this->assertEquals("mutable{Array<String>, ['hello', 'hello', 'hi', 'world']}", $result);
	}

	public function testSortNonEmptyStringsReverse(): void {
		$result = $this->executeCodeSnippet("mutable{Array<String>, ['hello','world', 'hi', 'hello']}->SORT[reverse: true];");
		$this->assertEquals("mutable{Array<String>, ['world', 'hi', 'hello', 'hello']}", $result);
	}

	public function testSortInvalidTargetType(): void {
		$this->executeErrorCodeSnippet('Invalid target type', "mutable{Array<Integer|String>, [12, 'hello','world', 'hi', 'hello']}->SORT;");
	}

	public function testSortInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('The parameter type Integer[1] is not a subtype of (Null|[reverse: Boolean])', "mutable{Array<Real>, [1, 2, 5.3, 2]}->SORT(1);");
	}

	public function testSortInvalidParameterTypeRecordKeys(): void {
		$this->executeErrorCodeSnippet('The parameter type [reverse: True, x: Integer[7]] is not a subtype of (Null|[reverse: Boolean])', "mutable{Array<Real>, [1, 2, 5.3, 2]}->SORT[reverse: true, x: 7];");
	}

	public function testSortInvalidParameterTypeRecordValue(): void {
		$this->executeErrorCodeSnippet('The parameter type [reverse: Integer[4]] is not a subtype of (Null|[reverse: Boolean])', "mutable{Array<Real>, [1, 2, 5.3, 2]}->SORT[reverse: 4];");
	}
}