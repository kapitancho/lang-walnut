<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\String;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class HtmlEscapeTest extends CodeExecutionTestHelper {

	public function testHtmlEscape(): void {
		$result = $this->executeCodeSnippet("'hello >'->htmlEscape;");
		$this->assertEquals("'hello &gt;'", $result);
	}

	public function testHtmlEscapeTypeMax(): void {
		$result = $this->executeCodeSnippet("he('hello >')",
			valueDeclarations: "he = ^s: String<2..10> => String<2..60> :: s->htmlEscape;");
		$this->assertEquals("'hello &gt;'", $result);
	}

	public function testHtmlEscapeTypeMaxInfinity(): void {
		$result = $this->executeCodeSnippet("he('hello >')",
			valueDeclarations: "he = ^s: String => String :: s->htmlEscape;");
		$this->assertEquals("'hello &gt;'", $result);
	}

}