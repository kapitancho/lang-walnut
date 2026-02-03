<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Array;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class ReverseTest extends CodeExecutionTestHelper {

	public function testReverseEmpty(): void {
		$result = $this->executeCodeSnippet("[]->reverse;");
		$this->assertEquals("[]", $result);
	}

	public function testReverseNonEmpty(): void {
		$result = $this->executeCodeSnippet("[1, 2]->reverse;");
		$this->assertEquals("[2, 1]", $result);
	}
}