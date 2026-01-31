<?php

namespace Walnut\Lang\Test\Almond\Engine\Implementation\Expression;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class NoErrorExpressionTest extends CodeExecutionTestHelper {

	public function testNoErrorSimple(): void {
		$result = $this->executeCodeSnippet("'ok'?;");
		$this->assertEquals("'ok'", $result);
	}

	public function testNoErrorResultReturn(): void {
		$declaration = <<<NUT
			noError = ^s: Result<String, Boolean> => Result<Integer, Boolean> :: s?->length;
		NUT;
		$result = $this->executeCodeSnippet("noError('ok');", valueDeclarations: $declaration);
		$this->assertEquals("2", $result);
	}

	public function testNoErrorResult(): void {
		$declaration = <<<NUT
			noError = ^s: Result<String, Boolean> => Result<Integer, Boolean> :: s?->length;
		NUT;

		$result = $this->executeCodeSnippet("noError(@false);", valueDeclarations: $declaration);
		$this->assertEquals("@false", $result);
	}
}