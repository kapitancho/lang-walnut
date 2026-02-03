<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\String;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class HtmlEscapeTest extends CodeExecutionTestHelper {

	public function testHtmlEscape(): void {
		$result = $this->executeCodeSnippet("'hello >'->htmlEscape;");
		$this->assertEquals("'hello &gt;'", $result);
	}

}