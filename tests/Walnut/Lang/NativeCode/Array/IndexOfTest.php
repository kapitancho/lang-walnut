<?php

namespace Walnut\Lang\Test\NativeCode\Array;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class IndexOfTest extends CodeExecutionTestHelper {

	public function testIndexOfEmpty(): void {
		$result = $this->executeCodeSnippet("[]->indexOf(5);");
		$this->assertEquals("@ItemNotFound", $result);
	}

	public function testIndexOfNonEmpty(): void {
		$result = $this->executeCodeSnippet("[1, 2, 5, 10, 5]->indexOf(5);");
		$this->assertEquals("2", $result);
	}
}