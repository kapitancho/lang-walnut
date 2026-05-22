<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Expression;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

/**
 * Tests for the @? operator which converts error values to empty.
 */
final class ErrorAsEmptyExpressionTest extends CodeExecutionTestHelper {

	public function testErrorAsEmptyOnSuccess(): void {
		$result = $this->executeCodeSnippet("5 @?;");
		$this->assertEquals("5", $result);
	}

	public function testErrorAsEmptyOnError(): void {
		$result = $this->executeCodeSnippet("@'failed' @?;");
		$this->assertEquals("empty", $result);
	}

	public function testErrorAsEmptyWithStringValue(): void {
		$result = $this->executeCodeSnippet("'hello' @?;");
		$this->assertEquals("'hello'", $result);
	}

	public function testErrorAsEmptyOnIntegerError(): void {
		$result = $this->executeCodeSnippet("@42 @?;");
		$this->assertEquals("empty", $result);
	}

	public function testErrorAsEmptyResultType(): void {
		$declaration = <<<NUT
			toOptional = ^s: Result<String, Boolean> => ?String :: s @?;
		NUT;
		$result = $this->executeCodeSnippet("toOptional('ok');", valueDeclarations: $declaration);
		$this->assertEquals("'ok'", $result);
	}

	public function testErrorAsEmptyResultTypeOnError(): void {
		$declaration = <<<NUT
			toOptional = ^s: Result<String, Boolean> => ?String :: s @?;
		NUT;
		$result = $this->executeCodeSnippet("toOptional(@false);", valueDeclarations: $declaration);
		$this->assertEquals("empty", $result);
	}

	public function testErrorAsEmptyWithFallback(): void {
		$result = $this->executeCodeSnippet("(@'err' @?) ?? 'default';");
		$this->assertEquals("'default'", $result);
	}
}
