<?php

namespace Walnut\Lang\NativeCode\String;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class ToUpperCaseTest extends CodeExecutionTestHelper {

	public function testToUpperCase(): void {
		$result = $this->executeCodeSnippet("'My Name'->toUpperCase;");
		$this->assertEquals("'MY NAME'", $result);
	}

}