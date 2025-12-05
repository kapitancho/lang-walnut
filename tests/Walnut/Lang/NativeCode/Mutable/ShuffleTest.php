<?php

namespace Walnut\Lang\NativeCode\Mutable;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class ShuffleTest extends CodeExecutionTestHelper {

	public function testShuffleEmpty(): void {
		$result = $this->executeCodeSnippet("mutable{Array, []}->shuffle;");
		$this->assertEquals("mutable{Array, []}", $result);
	}

	public function testShuffleNonEmpty(): void {
		$result = $this->executeCodeSnippet("mutable{Array, [1, 2, 5]}->shuffle->value->length;");
		$this->assertEquals("3", $result);
	}
}