<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Array;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class WithoutAllTest extends CodeExecutionTestHelper {

	public function testWithoutAllEmpty(): void {
		$result = $this->executeCodeSnippet("[]->withoutAll(3);");
		$this->assertEquals("[]", $result);
	}

	public function testWithoutAllNotFound(): void {
		$result = $this->executeCodeSnippet("[1, 2, 5, 10, 5]->withoutAll(3);");
		$this->assertEquals("[1, 2, 5, 10, 5]", $result);
	}

	public function testWithoutAllNonEmpty(): void {
		$result = $this->executeCodeSnippet("[1, 2, 5, 10, 5]->withoutAll(5);");
		$this->assertEquals("[1, 2, 10]", $result);
	}
}