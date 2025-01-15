<?php

namespace Walnut\Lang\NativeCode\Map;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class WithoutAllTest extends CodeExecutionTestHelper {

	public function testWithoutAllEmpty(): void {
		$result = $this->executeCodeSnippet("[:]->withoutAll(3);");
		$this->assertEquals("[:]", $result);
	}

	public function testWithoutAllNotFound(): void {
		$result = $this->executeCodeSnippet("[a: 1, b: 2, c: 5, d: 10, e: 5]->withoutAll(3);");
		$this->assertEquals("[a: 1, b: 2, c: 5, d: 10, e: 5]", $result);
	}

	public function testWithoutAllNonEmpty(): void {
		$result = $this->executeCodeSnippet("[a: 1, b: 2, c: 5, d: 10, e: 5]->withoutAll(5);");
		$this->assertEquals("[a: 1, b: 2, d: 10]", $result);
	}
}