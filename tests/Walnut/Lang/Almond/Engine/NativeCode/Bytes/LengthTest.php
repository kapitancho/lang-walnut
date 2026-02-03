<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Bytes;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class LengthTest extends CodeExecutionTestHelper {

	public function testLength(): void {
		$result = $this->executeCodeSnippet('"hello"->length;');
		$this->assertEquals("5", $result);
	}

}
