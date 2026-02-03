<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Result;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class ErrorTest extends CodeExecutionTestHelper {

	public function testErrorOk(): void {
		$result = $this->executeCodeSnippet("{@'error'}->error;");
		$this->assertEquals("'error'", $result);
	}

	public function testErrorReturnType(): void {
		$result = $this->executeCodeSnippet("err(@'error');",
			valueDeclarations: "err = ^e: Error<String> => String :: e->error;");
		$this->assertEquals("'error'", $result);
	}

}