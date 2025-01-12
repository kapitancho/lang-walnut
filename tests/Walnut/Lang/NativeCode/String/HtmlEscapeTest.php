<?php

namespace Walnut\Lang\NativeCode\String;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class HtmlEscapeTest extends CodeExecutionTestHelper {

	public function testHtmlEscape(): void {
		$result = $this->executeCodeSnippet("'hello >'->htmlEscape;");
		$this->assertEquals("'hello &gt;'", $result);
	}

}