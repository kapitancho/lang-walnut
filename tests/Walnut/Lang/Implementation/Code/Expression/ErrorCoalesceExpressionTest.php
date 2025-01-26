<?php

namespace Walnut\Lang\Implementation\Code\Expression;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class ErrorCoalesceExpressionTest extends CodeExecutionTestHelper {

	public function testErrorCoalesceWorkaround(): void {
		$result = $this->executeCodeSnippet("[errorCoalesce(true), errorCoalesce(@'hello')];", <<<NUT
			errorCoalesce = ^Result<Boolean, String> => Boolean|Integer :: ?whenTypeOf(#) is {
				type{Boolean}: #,
				type{Error<String>}: #->error->length
			};
		NUT);
		$this->assertEquals("[true, 5]", $result);
	}

	public function testErrorCoalesceExpression(): void {
		$result = $this->executeCodeSnippet("[errorCoalesce(true), errorCoalesce(@'hello')];", <<<NUT
			errorCoalesce = ^Result<Boolean, String> => Boolean|Integer :: 
				?whenIsError(#) {#->error->length};
		NUT);
		$this->assertEquals("[true, 5]", $result);
	}

	public function testErrorCoalesceExpressionWithElse(): void {
		$result = $this->executeCodeSnippet("[errorCoalesce(true), errorCoalesce(@'hello')];", <<<NUT
			errorCoalesce = ^Result<Boolean, String> => Boolean|Integer :: 
				?whenIsError(#) {#->error->length} ~ {!#};
		NUT);
		$this->assertEquals("[false, 5]", $result);
	}

}