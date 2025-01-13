<?php

namespace Walnut\Lang\NativeCode\Integer;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class BinaryLessThanTest extends CodeExecutionTestHelper {

	public function testBinaryLessThanTrue(): void {
		$result = $this->executeCodeSnippet("3 < 5;");
		$this->assertEquals("true", $result);
	}

	public function testBinaryLessThanSame(): void {
		$result = $this->executeCodeSnippet("3 < 3;");
		$this->assertEquals("false", $result);
	}

	public function testBinaryLessThanFalse(): void {
		$result = $this->executeCodeSnippet("5 < 3;");
		$this->assertEquals("false", $result);
	}
}