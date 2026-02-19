<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Bytes;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class SliceTest extends CodeExecutionTestHelper {

	public function testSubstringOk(): void {
		$result = $this->executeCodeSnippet('"hello"->slice[start: 1, length: 2];');
		$this->assertEquals('"el"', $result);
	}

	public function testSubstringInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Parameter type Integer[5] is not a subtype of [start: Integer<0..>, length: Integer<0..>]', '"hello"->slice(5);');
	}

	public function testSubstringInvalidParameterKeys(): void {
		$this->executeErrorCodeSnippet('Parameter type [length: Integer[10]] is not a subtype of [start: Integer<0..>, length: Integer<0..>]', '"hello"->slice[length: 10];');
	}

}
