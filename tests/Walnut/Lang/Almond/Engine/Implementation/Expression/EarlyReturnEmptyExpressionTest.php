<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Expression;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class EarlyReturnEmptyExpressionTest extends CodeExecutionTestHelper {

	public function testEarlyReturnEmptySimple(): void {
		$result = $this->executeCodeSnippet("'ok'?!;");
		$this->assertEquals("'ok'", $result);
	}

	public function testEarlyReturnEmptyResultReturn(): void {
		$declaration = <<<NUT
			noEmpty = ^s: ?String => ?Integer :: s?!->length;
		NUT;
		$result = $this->executeCodeSnippet("noEmpty('ok');", valueDeclarations: $declaration);
		$this->assertEquals("2", $result);
	}

	public function testEarlyReturnEmptyResult(): void {
		$declaration = <<<NUT
			noEmpty = ^s: ?String => ?Integer :: s?!->length;
		NUT;

		$result = $this->executeCodeSnippet("noEmpty(empty);", valueDeclarations: $declaration);
		$this->assertEquals("empty", $result);
	}
}