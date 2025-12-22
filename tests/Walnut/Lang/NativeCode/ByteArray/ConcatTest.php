<?php

namespace Walnut\Lang\Test\NativeCode\ByteArray;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class ConcatTest extends CodeExecutionTestHelper {

	public function testConcat(): void {
		$result = $this->executeCodeSnippet('"hello "->concat("world");');
		$this->assertEquals('"hello world"', $result);
	}

	public function testConcatInvalidParameter(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', '"hello"->concat(23);');
	}

}
