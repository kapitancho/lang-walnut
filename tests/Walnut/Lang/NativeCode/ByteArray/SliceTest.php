<?php

namespace Walnut\Lang\Test\NativeCode\ByteArray;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class SliceTest extends CodeExecutionTestHelper {

	public function testSubstringOk(): void {
		$result = $this->executeCodeSnippet('"hello"->slice[start: 1, length: 2];');
		$this->assertEquals('"el"', $result);
	}

	public function testSubstringInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', '"hello"->slice(5);');
	}

	public function testSubstringInvalidParameterKeys(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', '"hello"->slice[length: 10];');
	}

}
