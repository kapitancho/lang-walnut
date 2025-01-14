<?php

namespace Walnut\Lang\Test\NativeCode\Array;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class BinaryPlusTest extends CodeExecutionTestHelper {

	public function testBinaryPlusEmpty(): void {
		$result = $this->executeCodeSnippet("[] + [];");
		$this->assertEquals("[]", $result);
	}

	public function testBinaryPlusNonEmpty(): void {
		$result = $this->executeCodeSnippet("[1, 2] + [3, 4];");
		$this->assertEquals("[1, 2, 3, 4]", $result);
	}

	public function testBinaryPlusInvalidType(): void {
		$this->executeErrorCodeSnippet('Invalid target type', "[1, 2] + 3;");
	}
}