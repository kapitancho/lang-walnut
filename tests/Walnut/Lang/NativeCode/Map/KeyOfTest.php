<?php

namespace Walnut\Lang\NativeCode\Map;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class KeyOfTest extends CodeExecutionTestHelper {

	public function testKeyOfEmpty(): void {
		$result = $this->executeCodeSnippet("[:]->keyOf(5);");
		$this->assertEquals("@ItemNotFound()", $result);
	}

	public function testKeyOfNonEmpty(): void {
		$result = $this->executeCodeSnippet("[a: 1, b: 2, c: 5, d: 10, e: 5]->keyOf(5);");
		$this->assertEquals("'c'", $result);
	}
}