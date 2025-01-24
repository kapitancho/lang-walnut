<?php

namespace Walnut\Lang\NativeCode\String;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class BinaryGreaterThanEqualTest extends CodeExecutionTestHelper {

	public function testBinaryGreaterThanEqualFalse(): void {
		$result = $this->executeCodeSnippet("'abc' >= 'ac';");
		$this->assertEquals("false", $result);
	}

	public function testBinaryGreaterThanEqualSame(): void {
		$result = $this->executeCodeSnippet("'abc' >= 'abc';");
		$this->assertEquals("true", $result);
	}

	public function testBinaryGreaterThanEqualTrue(): void {
		$result = $this->executeCodeSnippet("'ac' >= 'abc';");
		$this->assertEquals("true", $result);
	}
}