<?php

namespace Walnut\Lang\NativeCode\Integer;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class BinaryGreaterThanTest extends CodeExecutionTestHelper {

	public function testBinaryGreaterThanTrue(): void {
		$result = $this->executeCodeSnippet("3 > 5;");
		$this->assertEquals("false", $result);
	}

	public function testBinaryGreaterThanSame(): void {
		$result = $this->executeCodeSnippet("3 > 3;");
		$this->assertEquals("false", $result);
	}

	public function testBinaryGreaterThanFalse(): void {
		$result = $this->executeCodeSnippet("5 > 3;");
		$this->assertEquals("true", $result);
	}
}