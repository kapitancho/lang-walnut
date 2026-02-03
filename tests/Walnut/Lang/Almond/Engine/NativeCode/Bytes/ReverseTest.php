<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Bytes;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class ReverseTest extends CodeExecutionTestHelper {

	public function testReverse(): void {
		$result = $this->executeCodeSnippet('"hello"->reverse;');
		$this->assertEquals('"olleh"', $result);
	}

}
