<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\String;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class AsBytesTest extends CodeExecutionTestHelper {

	public function testAsBytesOk(): void {
		$result = $this->executeCodeSnippet("'test'->asBytes;");
		$this->assertEquals('"test"', $result);
	}

}