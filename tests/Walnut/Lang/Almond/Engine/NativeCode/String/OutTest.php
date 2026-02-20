<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\String;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class OutTest extends CodeExecutionTestHelper {

	public function testOutBasic(): void {
		ob_start();
		$result = $this->executeCodeSnippet("'text'->OUT;");
		$this->assertEquals("'text'", $result);
		$output = ob_get_clean();
		$this->assertEquals("text", $output);
	}

	public function testHtmlFalse(): void {
		ob_start();
		$result = $this->executeCodeSnippet("'text<q/>\\nrow2'->OUT[html: false];");
		$this->assertEquals("'text<q/>\\nrow2'", $result);
		$output = ob_get_clean();
		$this->assertEquals("text<q/>\nrow2", $output);
	}

	public function testHtmlTrue(): void {
		ob_start();
		$result = $this->executeCodeSnippet("'text<q/>\\nrow2'->OUT[html: true];");
		$this->assertEquals("'text<q/>\\nrow2'", $result);
		$output = ob_get_clean();
		$this->assertEquals("text&lt;q/&gt;<br />\nrow2", $output);
	}

	public function testHtmlInvalidRecordParameterHtmlType(): void {
		$this->executeErrorCodeSnippet(
			"The parameter type [html: String['yes']] is not a subtype of the expected record type [html: Boolean]",
			"'text'->OUT[html: 'yes'];"
		);
	}

	public function testHtmlInvalidRecordParameterType(): void {
		$this->executeErrorCodeSnippet(
			"The parameter type [html: True, other: Integer[1]] is not a subtype of the expected record type [html: Boolean]",
			"'text'->OUT[html: true, other: 1];"
		);
	}

	public function testHtmlInvalidParameterType(): void {
		$this->executeErrorCodeSnippet(
			'Invalid parameter type: Integer[42]',
			"'text'->OUT(42);"
		);
	}
}