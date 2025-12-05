<?php

namespace Walnut\Lang\NativeCode\Mutable;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class ReverseTest extends CodeExecutionTestHelper {

	public function testReverseEmpty(): void {
		$result = $this->executeCodeSnippet("mutable{Array<Integer>, []}->reverse;");
		$this->assertEquals("mutable{Array<Integer>, []}", $result);
	}

	public function testReverseNonEmpty(): void {
		$result = $this->executeCodeSnippet("mutable{Array<Integer>, [1, 2]}->reverse;");
		$this->assertEquals("mutable{Array<Integer>, [2, 1]}", $result);
	}
}