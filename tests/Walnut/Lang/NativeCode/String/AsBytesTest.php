<?php

namespace Walnut\Lang\NativeCode\String;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class AsBytesTest extends CodeExecutionTestHelper {

	public function testAsBytesOk(): void {
		$result = $this->executeCodeSnippet("'test'->asBytes;");
		$this->assertEquals('"test"', $result);
	}

}