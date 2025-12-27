<?php

namespace Walnut\Lang\NativeCode\String;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class AsByteArrayTest extends CodeExecutionTestHelper {

	public function testAsByteArrayOk(): void {
		$result = $this->executeCodeSnippet("'test'->asByteArray;");
		$this->assertEquals('"test"', $result);
	}

}