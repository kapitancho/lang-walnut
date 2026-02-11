<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Any;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class DUMPTest extends CodeExecutionTestHelper {

	public function testDUMPPlain(): void {
		ob_start();
		$result = $this->executeCodeSnippet(
			"[1, 'Hello']->DUMP;",
		);
		$output = ob_get_clean();
		$this->assertEquals("[1, 'Hello']", $output);
		$this->assertEquals("[1, 'Hello']", $result);
	}

	public function testDUMPWithNull(): void {
		ob_start();
		$result = $this->executeCodeSnippet(
			"[1, 'Hello']->DUMP(null);",
		);
		$output = ob_get_clean();
		$this->assertEquals("[1, 'Hello']", $output);
		$this->assertEquals("[1, 'Hello']", $result);
	}

	public function testDUMPWithBothFalse(): void {
		ob_start();
		$result = $this->executeCodeSnippet(
			"[1, 'Hello']->DUMP[:];",
		);
		$output = ob_get_clean();
		$this->assertEquals("[1, 'Hello']", $output);
		$this->assertEquals("[1, 'Hello']", $result);
	}

	public function testDUMPWithBothFalseExplicit(): void {
		ob_start();
		$result = $this->executeCodeSnippet(
			"[1, 'Hello']->DUMP[html: false, newLine: false];",
		);
		$output = ob_get_clean();
		$this->assertEquals("[1, 'Hello']", $output);
		$this->assertEquals("[1, 'Hello']", $result);
	}

	public function testDUMPWithHtml(): void {
		ob_start();
		$result = $this->executeCodeSnippet(
			"'<b>Hello</b>'->DUMP[html: true];",
		);
		$output = ob_get_clean();
		$this->assertEquals("&#039;&lt;b&gt;Hello&lt;/b&gt;&#039;", $output);
		$this->assertEquals("'<b>Hello</b>'", $result);
	}

	public function testDUMPWithNewLine(): void {
		ob_start();
		$result = $this->executeCodeSnippet(
			"[1, 'Hello']->DUMP[newLine: true];",
		);
		$output = ob_get_clean();
		$this->assertEquals("[1, 'Hello']" . PHP_EOL, $output);
		$this->assertEquals("[1, 'Hello']", $result);
	}

	public function testDUMPWithHtmlAndNewLine(): void {
		ob_start();
		$result = $this->executeCodeSnippet(
			"'<b>Hello</b>'->DUMP[html: true, newLine: true];",
		);
		$output = ob_get_clean();
		$this->assertEquals("&#039;&lt;b&gt;Hello&lt;/b&gt;&#039;<br/>" . PHP_EOL, $output);
		$this->assertEquals("'<b>Hello</b>'", $result);
	}

	public function testDUMPInvalidParameterType(): void {
		$this->executeErrorCodeSnippet(
			"Invalid parameter type",
			"[1, 'Hello']->DUMP(42);"
		);
	}

}
