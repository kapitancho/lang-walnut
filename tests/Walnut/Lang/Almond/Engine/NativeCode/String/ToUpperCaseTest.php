<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\String;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class ToUpperCaseTest extends CodeExecutionTestHelper {

	public function testToUpperCase(): void {
		$result = $this->executeCodeSnippet("'My Name'->toUpperCase;");
		$this->assertEquals("'MY NAME'", $result);
	}

}