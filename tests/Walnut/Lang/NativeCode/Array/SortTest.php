<?php

namespace Walnut\Lang\Test\NativeCode\Array;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class SortTest extends CodeExecutionTestHelper {

	public function testSortEmpty(): void {
		$result = $this->executeCodeSnippet("[]->sort;");
		$this->assertEquals("[]", $result);
	}

	public function testSortNonEmptyNumbers(): void {
		$result = $this->executeCodeSnippet("[1, 2, 5.3, 2]->sort;");
		$this->assertEquals("[1, 2, 2, 5.3]", $result);
	}

	public function testSortNonEmptyStrings(): void {
		$result = $this->executeCodeSnippet("['hello','world', 'hi', 'hello']->sort;");
		$this->assertEquals("['hello', 'hello', 'hi', 'world']", $result);
	}

	public function testSortInvalidType(): void {
		$this->executeErrorCodeSnippet('Invalid target type', "[12, 'hello','world', 'hi', 'hello']->sort;");
	}
}