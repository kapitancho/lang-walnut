<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Expression;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

/**
 * Tests for the ?@ operator which converts empty values to errors.
 *
 * Syntax: `expr ?@ errorValue` - if expr is empty, returns @errorValue, otherwise expr
 */
final class EmptyAsErrorExpressionTest extends CodeExecutionTestHelper {

	public function testEmptyAsErrorOnValue(): void {
		$result = $this->executeCodeSnippet("5 ?@ 'missing';");
		$this->assertEquals("5", $result);
	}

	public function testEmptyAsErrorOnEmpty(): void {
		$result = $this->executeCodeSnippet("empty ?@ 'missing';");
		$this->assertEquals("@'missing'", $result);
	}

	public function testEmptyAsErrorWithStringValue(): void {
		$result = $this->executeCodeSnippet("'hello' ?@ 'missing';");
		$this->assertEquals("'hello'", $result);
	}

	public function testEmptyAsErrorWithIntegerError(): void {
		$result = $this->executeCodeSnippet("empty ?@ 404;");
		$this->assertEquals("@404", $result);
	}

	public function testEmptyAsErrorOptionalType(): void {
		$declaration = <<<NUT
			require = ^s: ?String => Result<String, String> :: s ?@ 'required';
		NUT;
		$result = $this->executeCodeSnippet("require('ok');", valueDeclarations: $declaration);
		$this->assertEquals("'ok'", $result);
	}

	public function testEmptyAsErrorOptionalTypeOnEmpty(): void {
		$declaration = <<<NUT
			require = ^s: ?String => Result<String, String> :: s ?@ 'required';
		NUT;
		$result = $this->executeCodeSnippet("require(empty);", valueDeclarations: $declaration);
		$this->assertEquals("@'required'", $result);
	}
}
