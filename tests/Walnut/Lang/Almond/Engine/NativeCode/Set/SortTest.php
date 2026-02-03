<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Set;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class SortTest extends CodeExecutionTestHelper {

	public function testSortEmpty(): void {
		$result = $this->executeCodeSnippet("[;]->sort;");
		$this->assertEquals("[;]", $result);
	}

	public function testSortNonEmptyNumbers(): void {
		$result = $this->executeCodeSnippet("[1; 2; 5.3; 2]->sort;");
		$this->assertEquals("[1; 2; 5.3]", $result);
	}

	public function testSortNonEmptyNumbersReverseRegular(): void {
		$result = $this->executeCodeSnippet("[1; 2; 5.3; 2]->sort[reverse: false];");
		$this->assertEquals("[1; 2; 5.3]", $result);
	}

	public function testSortNonEmptyNumbersReverse(): void {
		$result = $this->executeCodeSnippet("[1; 2; 5.3; 2]->sort[reverse: true];");
		$this->assertEquals("[5.3; 2; 1]", $result);
	}

	public function testSortNonEmptyStrings(): void {
		$result = $this->executeCodeSnippet("['hello'; 'world'; 'hi'; 'hello']->sort;");
		$this->assertEquals("['hello'; 'hi'; 'world']", $result);
	}

	public function testSortNonEmptyStringsReverseRegular(): void {
		$result = $this->executeCodeSnippet("['hello'; 'world'; 'hi'; 'hello']->sort[reverse: false];");
		$this->assertEquals("['hello'; 'hi'; 'world']", $result);
	}

	public function testSortNonEmptyStringsReverse(): void {
		$result = $this->executeCodeSnippet("['hello'; 'world'; 'hi'; 'hello']->sort[reverse: true];");
		$this->assertEquals("['world'; 'hi'; 'hello']", $result);
	}

	public function testSortInvalidTargetType(): void {
		$this->executeErrorCodeSnippet('Invalid target type', "[12; 'hello'; 'world'; 'hi'; 'hello']->sort;");
	}

	public function testSortInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('The parameter type Integer[1] is not a subtype of (Null|[reverse: Boolean])', "[1; 2; 5.3; 2]->sort(1);");
	}

	public function testSortInvalidParameterTypeRecordKeys(): void {
		$this->executeErrorCodeSnippet('The parameter type [reverse: True, x: Integer[7]] is not a subtype of (Null|[reverse: Boolean])', "[1; 2; 5.3; 2]->sort[reverse: true, x: 7];");
	}

	public function testSortInvalidParameterTypeRecordValue(): void {
		$this->executeErrorCodeSnippet('The parameter type [reverse: Integer[4]] is not a subtype of (Null|[reverse: Boolean])', "[1; 2; 5.3; 2]->sort[reverse: 4];");
	}
}