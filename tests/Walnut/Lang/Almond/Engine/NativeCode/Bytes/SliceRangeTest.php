<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Bytes;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class SliceRangeTest extends CodeExecutionTestHelper {

	public function testSubstringRangeOk(): void {
		$result = $this->executeCodeSnippet('"hello"->sliceRange[start: 1, end: 3];');
		$this->assertEquals('"el"', $result);
	}

	public function testSubstringRangeInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Parameter type Integer[5] is not a subtype of [start: Integer<0..>, end: Integer<0..>]', '"hello"->sliceRange(5);');
	}

	public function testSubstringRangeInvalidParameterKeys(): void {
		$this->executeErrorCodeSnippet('Parameter type [length: Integer[10]] is not a subtype of [start: Integer<0..>, end: Integer<0..>]', '"hello"->sliceRange[length: 10];');
	}

}
