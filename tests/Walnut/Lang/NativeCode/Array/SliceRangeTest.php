<?php

namespace Walnut\Lang\Test\NativeCode\Array;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class SliceRangeTest extends CodeExecutionTestHelper {

	public function testSliceRangeEmpty(): void {
		$result = $this->executeCodeSnippet("[]->sliceRange[start: 1, end: 3];");
		$this->assertEquals("[]", $result);
	}

	public function testSliceRangeNonEmpty(): void {
		$result = $this->executeCodeSnippet("[1, 2, 10, 11, 12, 13]->sliceRange[start: 1, end: 3];");
		$this->assertEquals("[2, 10]", $result);
	}

	public function testSliceRangeInvalidParameterType(): void {
		$this->executeErrorCodeSnippet(
			"Invalid parameter type: Integer[42]",
			"[1, 2, 10, 11, 12, 13]->sliceRange(42)");
	}

}