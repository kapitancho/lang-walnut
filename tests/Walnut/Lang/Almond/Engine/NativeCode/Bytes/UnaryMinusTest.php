<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Bytes;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class UnaryMinusTest extends CodeExecutionTestHelper {

	public function testReverse(): void {
		$result = $this->executeCodeSnippet('-"hello";');
		$this->assertEquals('"olleh"', $result);
	}

}
