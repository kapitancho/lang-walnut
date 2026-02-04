<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Mutable;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class SortTest extends CodeExecutionTestHelper {

	public function testArraySortEmpty(): void {
		$result = $this->executeCodeSnippet("mutable{Array<Integer>, []}->SORT;");
		$this->assertEquals("mutable{Array<Integer>, []}", $result);
	}

	public function testArraySortNonEmptyNumbers(): void {
		$result = $this->executeCodeSnippet("mutable{Array<Real>, [1, 2, 5.3, 2]}->SORT;");
		$this->assertEquals("mutable{Array<Real>, [1, 2, 2, 5.3]}", $result);
	}

	public function testArraySortNonEmptyNumbersReverseRegular(): void {
		$result = $this->executeCodeSnippet("mutable{Array<Real>, [1, 2, 5.3, 2]}->SORT[reverse: false];");
		$this->assertEquals("mutable{Array<Real>, [1, 2, 2, 5.3]}", $result);
	}

	public function testArraySortNonEmptyNumbersReverse(): void {
		$result = $this->executeCodeSnippet("mutable{Array<Real>, [1, 2, 5.3, 2]}->SORT[reverse: true];");
		$this->assertEquals("mutable{Array<Real>, [5.3, 2, 2, 1]}", $result);
	}

	public function testArraySortNonEmptyStrings(): void {
		$result = $this->executeCodeSnippet("mutable{Array<String>, ['hello','world', 'hi', 'hello']}->SORT;");
		$this->assertEquals("mutable{Array<String>, ['hello', 'hello', 'hi', 'world']}", $result);
	}

	public function testArraySortNonEmptyStringsReverseRegular(): void {
		$result = $this->executeCodeSnippet("mutable{Array<String>, ['hello','world', 'hi', 'hello']}->SORT[reverse: false];");
		$this->assertEquals("mutable{Array<String>, ['hello', 'hello', 'hi', 'world']}", $result);
	}

	public function testArraySortNonEmptyStringsReverse(): void {
		$result = $this->executeCodeSnippet("mutable{Array<String>, ['hello','world', 'hi', 'hello']}->SORT[reverse: true];");
		$this->assertEquals("mutable{Array<String>, ['world', 'hi', 'hello', 'hello']}", $result);
	}

	public function testArraySortInvalidTargetType(): void {
		$this->executeErrorCodeSnippet('Invalid target type', "mutable{Array<Integer|String>, [12, 'hello','world', 'hi', 'hello']}->SORT;");
	}

	public function testArraySortInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('The parameter type Integer[1] is not a subtype of (Null|[reverse: Boolean])', "mutable{Array<Real>, [1, 2, 5.3, 2]}->SORT(1);");
	}

	public function testArraySortInvalidParameterTypeRecordKeys(): void {
		$this->executeErrorCodeSnippet('The parameter type [reverse: True, x: Integer[7]] is not a subtype of (Null|[reverse: Boolean])', "mutable{Array<Real>, [1, 2, 5.3, 2]}->SORT[reverse: true, x: 7];");
	}

	public function testArraySortInvalidParameterTypeRecordValue(): void {
		$this->executeErrorCodeSnippet('The parameter type [reverse: Integer[4]] is not a subtype of (Null|[reverse: Boolean])', "mutable{Array<Real>, [1, 2, 5.3, 2]}->SORT[reverse: 4];");
	}



	public function testMapSortEmpty(): void {
		$result = $this->executeCodeSnippet("mutable{Map<Real>, [:]}->SORT;");
		$this->assertEquals("mutable{Map<Real>, [:]}", $result);
	}

	public function testMapSortNonEmptyNumbers(): void {
		$result = $this->executeCodeSnippet("mutable{Map<Real>, [a: 1, b: 21, c: 5.3, d: 2]}->SORT;");
		$this->assertEquals("mutable{Map<Real>, [a: 1, d: 2, c: 5.3, b: 21]}", $result);
	}

	public function testMapSortNonEmptyNumbersReverseRegular(): void {
		$result = $this->executeCodeSnippet("mutable{Map<Real>, [a: 1, b: 21, c: 5.3, d: 2]}->SORT[reverse: false];");
		$this->assertEquals("mutable{Map<Real>, [a: 1, d: 2, c: 5.3, b: 21]}", $result);
	}

	public function testMapSortNonEmptyNumbersReverse(): void {
		$result = $this->executeCodeSnippet("mutable{Map<Real>, [a: 1, b: 21, c: 5.3, d: 2]}->SORT[reverse: true];");
		$this->assertEquals("mutable{Map<Real>, [b: 21, c: 5.3, d: 2, a: 1]}", $result);
	}

	public function testMapSortNonEmptyStrings(): void {
		$result = $this->executeCodeSnippet("mutable{Map<String>, [a: 'hello', b: 'world', c: 'hi']}->SORT;");
		$this->assertEquals("mutable{Map<String>, [a: 'hello', c: 'hi', b: 'world']}", $result);
	}

	public function testMapSortNonEmptyStringsReverseRegular(): void {
		$result = $this->executeCodeSnippet("mutable{Map<String>, [a: 'hello', b: 'world', c: 'hi']}->SORT[reverse: false];");
		$this->assertEquals("mutable{Map<String>, [a: 'hello', c: 'hi', b: 'world']}", $result);
	}

	public function testMapSortNonEmptyStringsReverse(): void {
		$result = $this->executeCodeSnippet("mutable{Map<String>, [a: 'hello', b: 'world', c: 'hi']}->SORT[reverse: true];");
		$this->assertEquals("mutable{Map<String>, [b: 'world', c: 'hi', a: 'hello']}", $result);
	}

	public function testMapSortInvalidTargetType(): void {
		$this->executeErrorCodeSnippet('Invalid target type', "mutable{Map, [a: 12, b: 'hello', c: 'world', d: 'hi', e: 'hello']}->SORT;");
	}

	public function testMapSortInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('The parameter type Integer[1] is not a subtype of (Null|[reverse: Boolean])', "mutable{Map<Real>, [a: 1, b: 2, c: 5.3, d: 2]}->SORT(1);");
	}

	public function testMapSortInvalidParameterTypeRecordKeys(): void {
		$this->executeErrorCodeSnippet('The parameter type [reverse: True, x: Integer[7]] is not a subtype of (Null|[reverse: Boolean])', "mutable{Map<Real>, [a: 1, b: 2, c: 5.3, d: 2]}->SORT[reverse: true, x: 7];");
	}

	public function testMapSortInvalidParameterTypeRecordValue(): void {
		$this->executeErrorCodeSnippet('The parameter type [reverse: Integer[4]] is not a subtype of (Null|[reverse: Boolean])', "mutable{Map<Real>, [a: 1, b: 2, c: 5.3, d: 2]}->SORT[reverse: 4];");
	}


	public function testSetSortEmpty(): void {
		$result = $this->executeCodeSnippet("mutable{Set<Real>, [;]}->SORT;");
		$this->assertEquals("mutable{Set<Real>, [;]}", $result);
	}

	public function testSetSortNonEmptyNumbers(): void {
		$result = $this->executeCodeSnippet("mutable{Set<Real>, [1; 2; 5.3; 2]}->SORT;");
		$this->assertEquals("mutable{Set<Real>, [1; 2; 5.3]}", $result);
	}

	public function testSetSortNonEmptyNumbersReverseRegular(): void {
		$result = $this->executeCodeSnippet("mutable{Set<Real>, [1; 2; 5.3; 2]}->SORT[reverse: false];");
		$this->assertEquals("mutable{Set<Real>, [1; 2; 5.3]}", $result);
	}

	public function testSetSortNonEmptyNumbersReverse(): void {
		$result = $this->executeCodeSnippet("mutable{Set<Real>, [1; 2; 5.3; 2]}->SORT[reverse: true];");
		$this->assertEquals("mutable{Set<Real>, [5.3; 2; 1]}", $result);
	}

	public function testSetSortNonEmptyStrings(): void {
		$result = $this->executeCodeSnippet("mutable{Set<String>, ['hello'; 'world'; 'hi'; 'hello']}->SORT;");
		$this->assertEquals("mutable{Set<String>, ['hello'; 'hi'; 'world']}", $result);
	}

	public function testSetSortNonEmptyStringsReverseRegular(): void {
		$result = $this->executeCodeSnippet("mutable{Set<String>, ['hello'; 'world'; 'hi'; 'hello']}->SORT[reverse: false];");
		$this->assertEquals("mutable{Set<String>, ['hello'; 'hi'; 'world']}", $result);
	}

	public function testSetSortNonEmptyStringsReverse(): void {
		$result = $this->executeCodeSnippet("mutable{Set<String>, ['hello'; 'world'; 'hi'; 'hello']}->SORT[reverse: true];");
		$this->assertEquals("mutable{Set<String>, ['world'; 'hi'; 'hello']}", $result);
	}

	public function testSetSortInvalidTargetType(): void {
		$this->executeErrorCodeSnippet('Invalid target type', "mutable{Set, [12; 'hello'; 'world'; 'hi'; 'hello']}->SORT;");
	}

	public function testSetSortInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('The parameter type Integer[1] is not a subtype of (Null|[reverse: Boolean])', "mutable{Set<Real>, [1; 2; 5.3; 2]}->SORT(1);");
	}

	public function testSetSortInvalidParameterTypeRecordKeys(): void {
		$this->executeErrorCodeSnippet('The parameter type [reverse: True, x: Integer[7]] is not a subtype of (Null|[reverse: Boolean])', "mutable{Set<Real>, [1; 2; 5.3; 2]}->SORT[reverse: true, x: 7];");
	}

	public function testSetSortInvalidParameterTypeRecordValue(): void {
		$this->executeErrorCodeSnippet('The parameter type [reverse: Integer[4]] is not a subtype of (Null|[reverse: Boolean])', "mutable{Set<Real>, [1; 2; 5.3; 2]}->SORT[reverse: 4];");
	}
}