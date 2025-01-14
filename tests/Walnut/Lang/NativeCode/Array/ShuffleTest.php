<?php

namespace Walnut\Lang\NativeCode\Array;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class ShuffleTest extends CodeExecutionTestHelper {

	public function testShuffleEmpty(): void {
		$result = $this->executeCodeSnippet("[]->shuffle;");
		$this->assertEquals("[]", $result);
	}

	public function testShuffleNonEmpty(): void {
		$result = $this->executeCodeSnippet("[1, 2, 5]->shuffle->length;");
		$this->assertEquals("3", $result);
	}
}