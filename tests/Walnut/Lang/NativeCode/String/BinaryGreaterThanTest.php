<?php

namespace Walnut\Lang\NativeCode\String;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class BinaryGreaterThanTest extends CodeExecutionTestHelper {

	public function testBinaryGreaterThanFalse(): void {
		$result = $this->executeCodeSnippet("'abc' > 'ac';");
		$this->assertEquals("false", $result);
	}

	public function testBinaryGreaterThanSame(): void {
		$result = $this->executeCodeSnippet("'abc' > 'abc';");
		$this->assertEquals("false", $result);
	}

	public function testBinaryGreaterThanTrue(): void {
		$result = $this->executeCodeSnippet("'ac' > 'abc';");
		$this->assertEquals("true", $result);
	}
}