<?php

namespace Walnut\Lang\NativeCode\String;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class OutHtmlTest extends CodeExecutionTestHelper {

	public function testOutHtmlOk(): void {
		ob_start();
		$result = $this->executeCodeSnippet("'text\n>'->OUT_HTML;");
		$this->assertEquals("'text\\n>'", $result);
		$output = ob_get_clean();
		$this->assertEquals("text<br />\n&gt;", $output);
	}
}