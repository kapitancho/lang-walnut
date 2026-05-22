<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Expression;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class MatchEmptyExpressionTest extends CodeExecutionTestHelper {

	public function testMatchEmptySimpleOk(): void {
		$result = $this->executeCodeSnippet("?whenIsEmpty('ok') { 1 };");
		$this->assertEquals("'ok'", $result);
	}

	public function testMatchEmptySimpleOkElse(): void {
		$result = $this->executeCodeSnippet("?whenIsEmpty('ok') { 1 } ~ { 2 };");
		$this->assertEquals("2", $result);
	}

	public function testMatchEmptySimpleEmpty(): void {
		$result = $this->executeCodeSnippet("?whenIsEmpty(empty) { 1 };");
		$this->assertEquals("1", $result);
	}

	public function testMatchEmptySimpleEmptyElse(): void {
		$result = $this->executeCodeSnippet("?whenIsEmpty(empty) { 1 } ~ { 2 };");
		$this->assertEquals("1", $result);
	}

	public function testMatchEmptyOptionalReturn(): void {
		$declaration = <<<NUT
			noEmpty = ^s: ?String => String|Integer :: ?whenIsEmpty(s) { 0 } ~ { s };
		NUT;
		$result = $this->executeCodeSnippet("noEmpty('ok');", valueDeclarations: $declaration);
		$this->assertEquals("'ok'", $result);
	}

	public function testMatchEmptyOptional(): void {
		$declaration = <<<NUT
			noEmpty = ^s: ?String => String|Integer :: ?whenIsEmpty(s) { 0 } ~ { s };
		NUT;
		$result = $this->executeCodeSnippet("noEmpty(empty);", valueDeclarations: $declaration);
		$this->assertEquals("0", $result);
	}
}
