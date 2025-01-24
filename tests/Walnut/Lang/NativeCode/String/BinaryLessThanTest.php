<?php

namespace Walnut\Lang\NativeCode\String;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class BinaryLessThanTest extends CodeExecutionTestHelper {

	public function testBinaryLessThanFalse(): void {
		$result = $this->executeCodeSnippet("'ac' < 'abc';");
		$this->assertEquals("false", $result);
	}

	public function testBinaryLessThanSame(): void {
		$result = $this->executeCodeSnippet("'abc' < 'abc';");
		$this->assertEquals("false", $result);
	}

	public function testBinaryLessThanTrue(): void {
		$result = $this->executeCodeSnippet("'abc' < 'ac';");
		$this->assertEquals("true", $result);
	}
}