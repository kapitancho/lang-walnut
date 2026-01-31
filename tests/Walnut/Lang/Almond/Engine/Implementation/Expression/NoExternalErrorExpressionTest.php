<?php

namespace Walnut\Lang\Test\Almond\Engine\Implementation\Expression;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class NoExternalErrorExpressionTest extends CodeExecutionTestHelper {

	public function testNoExternalErrorSimple(): void {
		$result = $this->executeCodeSnippet("'ok'*?;");
		$this->assertEquals("'ok'", $result);
	}

	public function testNoExternalErrorResultReturn(): void {
		$declaration = <<<NUT
			noExternalError = ^v: Impure<String> => Impure<Integer> :: v *?->length;
		NUT;
		$result = $this->executeCodeSnippet("noExternalError('ok');", valueDeclarations: $declaration);
		$this->assertEquals("2", $result);
	}

	public function testNoExternalErrorResult(): void {
		$declaration = <<<NUT
			noExternalError = ^Impure<String> => Impure<Integer> :: #*?->length;
		NUT;

		$result = $this->executeCodeSnippet("@ExternalError[errorType: 'Error', originalError: 'Error', errorMessage: 'Error']*?;", valueDeclarations: $declaration);
		$this->assertEquals("@ExternalError[errorType: 'Error',originalError: 'Error',errorMessage: 'Error']",
			str_replace(["\n", "\t"], "", $result));
	}

	public function testNoExternalErrorUnion(): void {
		$declaration = <<<NUT
			noExternalError = ^v: Impure<Result<String, Integer>> => Impure<Boolean> :: v*?->asBoolean;
		NUT;

		$result = $this->executeCodeSnippet("noExternalError(@0);", valueDeclarations: $declaration);
		$this->assertEquals("true", $result);
	}

	public function testNoExternalErrorOtherErrorType(): void {
		$declaration = <<<NUT
			noExternalError = ^v: Result<String, Real> => Result<String, Real> :: v*?;
		NUT;
		$result = $this->executeCodeSnippet("noExternalError('ok');", valueDeclarations: $declaration);
		$this->assertEquals("'ok'", $result);
	}

	public function testNoExternalErrorOtherType(): void {
		$declaration = <<<NUT
			noExternalError = ^s: String => Integer :: s*?->length;
		NUT;
		$result = $this->executeCodeSnippet("noExternalError('ok');", valueDeclarations: $declaration);
		$this->assertEquals("2", $result);
	}
}