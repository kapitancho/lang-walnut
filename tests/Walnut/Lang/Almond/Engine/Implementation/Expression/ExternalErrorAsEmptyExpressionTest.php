<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Expression;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

/**
 * Tests for the *? operator which converts external error values to empty.
 */
final class ExternalErrorAsEmptyExpressionTest extends CodeExecutionTestHelper {

	public function testExternalErrorAsEmptyOnSuccess(): void {
		$result = $this->executeCodeSnippet("5 *?;");
		$this->assertEquals("5", $result);
	}

	public function testExternalErrorAsEmptyOnExternalError(): void {
		$result = $this->executeCodeSnippet(
			"@ExternalError[errorType: 'IO', originalError: 'x', errorMessage: 'fail'] *?;"
		);
		$this->assertEquals("empty", $result);
	}

	public function testExternalErrorAsEmptyWithStringValue(): void {
		$result = $this->executeCodeSnippet("'hello' *?;");
		$this->assertEquals("'hello'", $result);
	}

	public function testExternalErrorAsEmptyImpureType(): void {
		$declaration = <<<NUT
			toOptional = ^s: String* => ?String :: s *?;
		NUT;
		$result = $this->executeCodeSnippet("toOptional('ok');", valueDeclarations: $declaration);
		$this->assertEquals("'ok'", $result);
	}

	public function testExternalErrorAsEmptyImpureTypeOnError(): void {
		$declaration = <<<NUT
			toOptional = ^s: String* => ?String :: s *?;
		NUT;
		$result = $this->executeCodeSnippet(
			"toOptional(@ExternalError[errorType: 'IO', originalError: 'x', errorMessage: 'fail']);",
			valueDeclarations: $declaration
		);
		$this->assertEquals("empty", $result);
	}

	public function testExternalErrorAsEmptyWithFallback(): void {
		$result = $this->executeCodeSnippet(
			"(@ExternalError[errorType: 'IO', originalError: 'x', errorMessage: 'fail'] *?) ?? 'default';"
		);
		$this->assertEquals("'default'", $result);
	}
}
