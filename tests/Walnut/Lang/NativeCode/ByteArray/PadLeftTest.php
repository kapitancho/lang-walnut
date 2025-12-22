<?php

namespace Walnut\Lang\Test\NativeCode\ByteArray;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class PadLeftTest extends CodeExecutionTestHelper {

	public function testPadLeftOk(): void {
		$result = $this->executeCodeSnippet('"hello"->padLeft[length: 10, padByteArray: "-"];');
		$this->assertEquals('"-----hello"', $result);
	}

	public function testPadLeftNotNeeded(): void {
		$result = $this->executeCodeSnippet('"hello"->padLeft[length: 4, padByteArray: "-"];');
		$this->assertEquals('"hello"', $result);
	}

	public function testPadLeftInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', '"hello"->padLeft(5);');
	}

	public function testPadLeftInvalidParameterKeys(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', '"hello"->padLeft[length: 10];');
	}

}
