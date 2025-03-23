<?php

namespace Walnut\Lang\Test\Implementation\Code\Expression;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class NoExternalErrorExpressionTest extends CodeExecutionTestHelper {

	public function testNoExternalErrorSimple(): void {
		$result = $this->executeCodeSnippet("?noExternalError('ok');");
		$this->assertEquals("'ok'", $result);
	}

	public function testNoExternalErrorResultReturn(): void {
		$declaration = <<<NUT
			noExternalError = ^Impure<String> => Impure<Integer> :: {?noExternalError(#)}->length;
		NUT;
		$result = $this->executeCodeSnippet("noExternalError('ok');", $declaration);
		$this->assertEquals("2", $result);
	}

	public function testNoExternalErrorResult(): void {
		$declaration = <<<NUT
			noExternalError = ^Impure<String> => Impure<Integer> :: {?noExternalError(#)}->length;
		NUT;

		$result = $this->executeCodeSnippet("noExternalError(@ExternalError[errorType: 'Error', originalError: 'Error', errorMessage: 'Error']);", $declaration);
		$this->assertEquals("@ExternalError[errorType: 'Error',originalError: 'Error',errorMessage: 'Error']",
			str_replace(["\n", "\t"], "", $result));
	}

	public function testNoExternalErrorUnion(): void {
		$declaration = <<<NUT
			noExternalError = ^Impure<Result<String, Integer>> => Impure<Boolean> :: {?noExternalError(#)}->asBoolean;
		NUT;

		$result = $this->executeCodeSnippet("noExternalError(@0);", $declaration);
		$this->assertEquals("true", $result);
	}

	//TODO - better analysis may find out the ExternalError is not possible
	public function testNoExternalErrorOtherErrorType(): void {
		$declaration = <<<NUT
			noExternalError = ^Result<String, Real> => Result<String, Real|ExternalError> :: ?noExternalError(#);
		NUT;
		$result = $this->executeCodeSnippet("noExternalError('ok');", $declaration);
		$this->assertEquals("'ok'", $result);
	}

	public function testNoExternalErrorOtherType(): void {
		$declaration = <<<NUT
			noExternalError = ^String => Integer :: {?noExternalError(#)}->length;
		NUT;
		$result = $this->executeCodeSnippet("noExternalError('ok');", $declaration);
		$this->assertEquals("2", $result);
	}
}