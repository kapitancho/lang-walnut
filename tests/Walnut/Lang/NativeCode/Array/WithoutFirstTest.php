<?php

namespace Walnut\Lang\Test\NativeCode\Array;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class WithoutFirstTest extends CodeExecutionTestHelper {

	public function testWithoutFirstEmpty(): void {
		$result = $this->executeCodeSnippet("[]->withoutFirst;");
		$this->assertEquals("@ItemNotFound()", $result);
	}

	public function testWithoutFirstNonEmpty(): void {
		$result = $this->executeCodeSnippet("[1, 2]->withoutFirst;");
		$this->assertEquals("[element: 1, array: [2]]", $result);
	}
}