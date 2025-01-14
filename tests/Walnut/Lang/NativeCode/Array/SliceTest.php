<?php

namespace Walnut\Lang\Test\NativeCode\Array;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class SliceTest extends CodeExecutionTestHelper {

	public function testSliceEmpty(): void {
		$result = $this->executeCodeSnippet("[]->slice[start: 1, length: 2];");
		$this->assertEquals("[]", $result);
	}

	public function testSliceNonEmpty(): void {
		$result = $this->executeCodeSnippet("[1, 2, 10, 11, 12, 13]->slice[start: 1, length: 2];");
		$this->assertEquals("[2, 10]", $result);
	}
}