<?php

namespace Walnut\Lang\NativeCode\ByteArray;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class UnaryMinusTest extends CodeExecutionTestHelper {

	public function testReverse(): void {
		$result = $this->executeCodeSnippet('-"hello";');
		$this->assertEquals('"olleh"', $result);
	}

}
