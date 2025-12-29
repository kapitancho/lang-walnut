<?php

namespace Walnut\Lang\Test\NativeCode\Bytes;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class SliceRangeTest extends CodeExecutionTestHelper {

	public function testSubstringRangeOk(): void {
		$result = $this->executeCodeSnippet('"hello"->sliceRange[start: 1, end: 3];');
		$this->assertEquals('"el"', $result);
	}

	public function testSubstringRangeInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', '"hello"->sliceRange(5);');
	}

	public function testSubstringRangeInvalidParameterKeys(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', '"hello"->sliceRange[length: 10];');
	}

}
