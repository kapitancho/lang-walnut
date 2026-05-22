<?php

namespace Walnut\Lang\Test\Almond\Engine\Implementation\Expression;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class EarlyReturnExternalErrorExpressionTest extends CodeExecutionTestHelper {

	public function testEarlyReturnExternalErrorSimple(): void {
		$result = $this->executeCodeSnippet("'ok'*!;");
		$this->assertEquals("'ok'", $result);
	}

	public function testEarlyReturnExternalErrorResultReturn(): void {
		$declaration = <<<NUT
			noExternalError = ^v: String* => Integer* :: v *!->length;
		NUT;
		$result = $this->executeCodeSnippet("noExternalError('ok');", valueDeclarations: $declaration);
		$this->assertEquals("2", $result);
	}

	public function testEarlyReturnExternalErrorResult(): void {
		$declaration = <<<NUT
			noExternalError = ^String* => Integer* :: #*!->length;
		NUT;

		$result = $this->executeCodeSnippet("@ExternalError[errorType: 'Error', originalError: 'Error', errorMessage: 'Error']*!;", valueDeclarations: $declaration);
		$this->assertEquals("@ExternalError[errorType: 'Error',originalError: 'Error',errorMessage: 'Error']",
			str_replace(["\n", "\t"], "", $result));
	}

	public function testEarlyReturnExternalErrorUnion(): void {
		$declaration = <<<NUT
			noExternalError = ^v: Result<String, Integer>* => Boolean* :: v*!->asBoolean;
		NUT;

		$result = $this->executeCodeSnippet("noExternalError(@0);", valueDeclarations: $declaration);
		$this->assertEquals("true", $result);
	}

	public function testEarlyReturnExternalErrorOtherErrorType(): void {
		$declaration = <<<NUT
			noExternalError = ^v: Result<String, Real> => Result<String, Real> :: v*!;
		NUT;
		$result = $this->executeCodeSnippet("noExternalError('ok');", valueDeclarations: $declaration);
		$this->assertEquals("'ok'", $result);
	}

	public function testEarlyReturnExternalErrorOtherType(): void {
		$declaration = <<<NUT
			noExternalError = ^s: String => Integer :: s*!->length;
		NUT;
		$result = $this->executeCodeSnippet("noExternalError('ok');", valueDeclarations: $declaration);
		$this->assertEquals("2", $result);
	}
}