<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Random;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class UuidTest extends CodeExecutionTestHelper {

	public function testUuidOk(): void {
		$result = $this->executeCodeSnippet("Random->uuid;");
		$this->assertEquals(44, strlen($result));
	}

}