<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\String;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class OutTextTest extends CodeExecutionTestHelper {

	public function testOutTextOk(): void {
		ob_start();
		$result = $this->executeCodeSnippet("'text'->OUT_TXT;");
		$this->assertEquals("'text'", $result);
		$output = ob_get_clean();
		$this->assertEquals("text", $output);
	}
}