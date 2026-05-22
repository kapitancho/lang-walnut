<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Expression;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class MatchExternalErrorExpressionTest extends CodeExecutionTestHelper {

	public function testMatchExternalErrorSimpleOk(): void {
		$result = $this->executeCodeSnippet("?whenIsExternalError('ok') { 1 };");
		$this->assertEquals("'ok'", $result);
	}

	public function testMatchExternalErrorSimpleOkElse(): void {
		$result = $this->executeCodeSnippet("?whenIsExternalError('ok') { 1 } ~ { 2 };");
		$this->assertEquals("2", $result);
	}

	public function testMatchExternalErrorSimpleError(): void {
		$result = $this->executeCodeSnippet(
			"?whenIsExternalError(@ExternalError[errorType: 'IO', originalError: 'x', errorMessage: 'fail']) { 1 };"
		);
		$this->assertEquals("1", $result);
	}

	public function testMatchExternalErrorSimpleErrorElse(): void {
		$result = $this->executeCodeSnippet(
			"?whenIsExternalError(@ExternalError[errorType: 'IO', originalError: 'x', errorMessage: 'fail']) { 1 } ~ { 2 };"
		);
		$this->assertEquals("1", $result);
	}

	public function testMatchExternalErrorWithExternalReturnType(): void {
		$declaration = <<<NUT
			noExternalError = ^s: String* => Integer :: ?whenIsExternalError(s) { 0 } ~ { s->length };
		NUT;
		$result = $this->executeCodeSnippet("noExternalError('ok');", valueDeclarations: $declaration);
		$this->assertEquals("2", $result);
	}

	public function testMatchExternalErrorWithExternalReturnTypeOnError(): void {
		$declaration = <<<NUT
			noExternalError = ^s: String* => Integer :: ?whenIsExternalError(s) { 0 } ~ { s->length };
		NUT;
		$result = $this->executeCodeSnippet(
			"noExternalError(@ExternalError[errorType: 'IO', originalError: 'x', errorMessage: 'fail']);",
			valueDeclarations: $declaration
		);
		$this->assertEquals("0", $result);
	}
}
