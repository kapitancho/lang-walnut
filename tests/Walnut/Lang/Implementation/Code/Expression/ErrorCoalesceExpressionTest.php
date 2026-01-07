<?php

namespace Walnut\Lang\Test\Implementation\Code\Expression;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class ErrorCoalesceExpressionTest extends CodeExecutionTestHelper {

	public function testErrorCoalesceWorkaround(): void {
		$result = $this->executeCodeSnippet("[errorCoalesce(true), errorCoalesce(@'hello')];", valueDeclarations: <<<NUT
			errorCoalesce = ^Result<Boolean, String> => Boolean|Integer :: ?whenTypeOf(#) {
				type{Boolean}: #,
				type{Error<String>}: #->error->length
			};
		NUT);
		$this->assertEquals("[true, 5]", $result);
	}

	public function testErrorCoalesceExpression(): void {
		$result = $this->executeCodeSnippet("[errorCoalesce(true), errorCoalesce(@'hello')];", valueDeclarations: <<<NUT
			errorCoalesce = ^v: Result<Boolean, String> => Boolean|Integer :: 
				?whenIsError(v) {v->error->length};
		NUT);
		$this->assertEquals("[true, 5]", $result);
	}

	public function testErrorCoalesceExpressionWithElse(): void {
		$result = $this->executeCodeSnippet("[errorCoalesce(true), errorCoalesce(@'hello')];", valueDeclarations: <<<NUT
			errorCoalesce = ^v: Result<Boolean, String> => Boolean|Integer :: 
				?whenIsError(v) {v->error->length} ~ {!v};
		NUT);
		$this->assertEquals("[false, 5]", $result);
	}

	public function testErrorCoalesceIfError(): void {
		$result = $this->executeCodeSnippet("[errorCoalesce(true), errorCoalesce(@'hello')];", valueDeclarations: <<<NUT
			errorCoalesce = ^v: Result<Boolean, String> => Boolean|Integer :: 
				v->ifError(^s: String => Integer :: s->length);
		NUT);
		$this->assertEquals("[true, 5]", $result);
	}

	public function testErrorCoalesceOrElse(): void {
		$result = $this->executeCodeSnippet("[errorCoalesce(true), errorCoalesce(@'hello')];", valueDeclarations: <<<NUT
			errorCoalesce = ^v: Result<Boolean, String> => Boolean|Integer :: 
				v->binaryOrElse(5);
		NUT);
		$this->assertEquals("[true, 5]", $result);
	}

	public function testErrorCoalesceOrElseQ(): void {
		$result = $this->executeCodeSnippet("[errorCoalesce(true), errorCoalesce(@'hello')];", valueDeclarations: <<<NUT
			errorCoalesce = ^v: Result<Boolean, String> => Boolean|Integer :: 
				v ?? 5;
		NUT);
		$this->assertEquals("[true, 5]", $result);
	}

}