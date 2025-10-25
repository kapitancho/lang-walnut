<?php

namespace Walnut\Lang\Test\NativeCode\Map;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class BinaryPlusTest extends CodeExecutionTestHelper {

	public function testBinaryPlusEmpty(): void {
		$result = $this->executeCodeSnippet("[:] + [:];");
		$this->assertEquals("[:]", $result);
	}

	public function testBinaryPlusNonEmpty(): void {
		$result = $this->executeCodeSnippet("[a: 1, b: 2] + [b: 3, c: 4];");
		$this->assertEquals("[a: 1, b: 3, c: 4]", $result);
	}

	public function testBinaryPlusInvalidType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "[a: 1, b: 2] + 3;");
	}
}