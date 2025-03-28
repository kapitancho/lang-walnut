<?php

namespace Walnut\Lang\Test\NativeCode\Array;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class LastIndexOfTest extends CodeExecutionTestHelper {

	public function testLastIndexOfEmpty(): void {
		$result = $this->executeCodeSnippet("[]->lastIndexOf(5);");
		$this->assertEquals("@ItemNotFound()", $result);
	}

	public function testLastIndexOfNonEmpty(): void {
		$result = $this->executeCodeSnippet("[1, 2, 5, 10, 5]->lastIndexOf(5);");
		$this->assertEquals("4", $result);
	}
}