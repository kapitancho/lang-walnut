<?php

namespace Walnut\Lang\NativeCode\Random;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class UuidTest extends CodeExecutionTestHelper {

	public function testUuidOk(): void {
		$result = $this->executeCodeSnippet("Random->uuid;");
		$this->assertEquals(38, strlen($result));
	}

}