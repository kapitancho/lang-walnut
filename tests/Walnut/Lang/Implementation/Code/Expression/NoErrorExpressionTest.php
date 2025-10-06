<?php

namespace Walnut\Lang\Test\Implementation\Code\Expression;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class NoErrorExpressionTest extends CodeExecutionTestHelper {

	public function testNoErrorSimple(): void {
		$result = $this->executeCodeSnippet("?noError('ok');");
		$this->assertEquals("'ok'", $result);
	}

	public function testNoErrorResultReturn(): void {
		$declaration = <<<NUT
			noError = ^Result<String, Boolean> => Result<Integer, Boolean> :: {?noError(#)}->length;
		NUT;
		$result = $this->executeCodeSnippet("noError('ok');", valueDeclarations: $declaration);
		$this->assertEquals("2", $result);
	}

	public function testNoErrorResult(): void {
		$declaration = <<<NUT
			noError = ^Result<String, Boolean> => Result<Integer, Boolean> :: {?noError(#)}->length;
		NUT;

		$result = $this->executeCodeSnippet("noError(@false);", valueDeclarations: $declaration);
		$this->assertEquals("@false", $result);
	}
}