<?php

namespace Walnut\Lang\NativeCode\Map;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class LengthTest extends CodeExecutionTestHelper {

	public function testLengthEmpty(): void {
		$result = $this->executeCodeSnippet("[:]->length;");
		$this->assertEquals("0", $result);
	}

	public function testLengthNonEmpty(): void {
		$result = $this->executeCodeSnippet("[a: 1, b: 2]->length;");
		$this->assertEquals("2", $result);
	}
}