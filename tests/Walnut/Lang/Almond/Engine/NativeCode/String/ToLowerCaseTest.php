<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\String;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class ToLowerCaseTest extends CodeExecutionTestHelper {

	public function testToLowerCase(): void {
		$result = $this->executeCodeSnippet("'My Name'->toLowerCase;");
		$this->assertEquals("'my name'", $result);
	}

}