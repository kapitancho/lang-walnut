<?php

namespace Walnut\Lang\Test\NativeCode\Array;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class WithoutLastTest extends CodeExecutionTestHelper {

	public function testWithoutLastEmpty(): void {
		$result = $this->executeCodeSnippet("[]->withoutLast;");
		$this->assertEquals("@ItemNotFound", $result);
	}

	public function testWithoutLastNonEmpty(): void {
		$result = $this->executeCodeSnippet("[1, 2]->withoutLast;");
		$this->assertEquals("[element: 2, array: [1]]", $result);
	}
}