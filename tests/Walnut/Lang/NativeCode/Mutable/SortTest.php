<?php

namespace Walnut\Lang\NativeCode\Mutable;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class SortTest extends CodeExecutionTestHelper {

	public function testSortEmpty(): void {
		$result = $this->executeCodeSnippet("mutable{Array<Integer>, []}->sort;");
		$this->assertEquals("mutable{Array<Integer>, []}", $result);
	}

	public function testSortNonEmptyNumbers(): void {
		$result = $this->executeCodeSnippet("mutable{Array<Real>, [1, 2, 5.3, 2]}->sort;");
		$this->assertEquals("mutable{Array<Real>, [1, 2, 2, 5.3]}", $result);
	}

	public function testSortNonEmptyStrings(): void {
		$result = $this->executeCodeSnippet("mutable{Array<String>, ['hello','world', 'hi', 'hello']}->sort;");
		$this->assertEquals("mutable{Array<String>, ['hello', 'hello', 'hi', 'world']}", $result);
	}

	public function testSortInvalidType(): void {
		$this->executeErrorCodeSnippet('Invalid target type', "mutable{Array<Integer|String>, [12, 'hello','world', 'hi', 'hello']}->sort;");
	}
}