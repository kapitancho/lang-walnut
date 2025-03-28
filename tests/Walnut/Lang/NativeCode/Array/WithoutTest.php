<?php

namespace Walnut\Lang\Test\NativeCode\Array;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class WithoutTest extends CodeExecutionTestHelper {

	public function testWithoutEmpty(): void {
		$result = $this->executeCodeSnippet("[]->without(3);");
		$this->assertEquals("@ItemNotFound()", $result);
	}

	public function testWithoutNotFound(): void {
		$result = $this->executeCodeSnippet("[1, 2, 5, 10, 5]->without(3);");
		$this->assertEquals("@ItemNotFound()", $result);
	}

	public function testWithoutNonEmpty(): void {
		$result = $this->executeCodeSnippet("[1, 2, 5, 10, 5]->without(5);");
		$this->assertEquals("[1, 2, 10, 5]", $result);
	}
}