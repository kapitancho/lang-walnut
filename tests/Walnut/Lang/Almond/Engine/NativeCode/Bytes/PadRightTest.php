<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Bytes;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class PadRightTest extends CodeExecutionTestHelper {

	public function testPadRightOk(): void {
		$result = $this->executeCodeSnippet('"hello"->padRight[length: 10, padBytes: "-"];');
		$this->assertEquals('"hello-----"', $result);
	}

	public function testPadRightNotNeeded(): void {
		$result = $this->executeCodeSnippet('"hello"->padRight[length: 4, padBytes: "-"];');
		$this->assertEquals('"hello"', $result);
	}

	public function testPadRightInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', '"hello"->padRight(5);');
	}

	public function testPadRightInvalidParameterKeys(): void {
		$this->executeErrorCodeSnippet('arameter type [length: Integer[10]] is not a subtype of [length: Integer, padBytes: Bytes]', '"hello"->padRight[length: 10];');
	}

}
