<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Mutable;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class ShuffleTest extends CodeExecutionTestHelper {

	public function testShuffleEmpty(): void {
		$result = $this->executeCodeSnippet("mutable{Array, []}->SHUFFLE;");
		$this->assertEquals("mutable{Array, []}", $result);
	}

	public function testShuffleNonEmpty(): void {
		$result = $this->executeCodeSnippet("mutable{Array, [1, 2, 5]}->SHUFFLE->value->length;");
		$this->assertEquals("3", $result);
	}
}