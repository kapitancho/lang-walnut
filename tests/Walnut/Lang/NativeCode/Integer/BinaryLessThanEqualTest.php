<?php

namespace Walnut\Lang\NativeCode\Integer;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class BinaryLessThanEqualTest extends CodeExecutionTestHelper {

	public function testBinaryLessThanEqualTrue(): void {
		$result = $this->executeCodeSnippet("3 <= 5;");
		$this->assertEquals("true", $result);
	}

	public function testBinaryLessThanEqualSame(): void {
		$result = $this->executeCodeSnippet("3 <= 3;");
		$this->assertEquals("true", $result);
	}

	public function testBinaryLessThanEqualFalse(): void {
		$result = $this->executeCodeSnippet("5 <= 3;");
		$this->assertEquals("false", $result);
	}
}