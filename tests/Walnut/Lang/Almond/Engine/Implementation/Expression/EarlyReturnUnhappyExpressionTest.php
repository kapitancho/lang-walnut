<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Expression;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

/**
 * Tests for the unhappy-path early return operator `expr!` which
 * returns immediately on empty OR error.
 */
final class EarlyReturnUnhappyExpressionTest extends CodeExecutionTestHelper {

	public function testEarlyReturnUnhappySimple(): void {
		$result = $this->executeCodeSnippet("'ok'!;");
		$this->assertEquals("'ok'", $result);
	}

	public function testEarlyReturnUnhappyWithErrorReturn(): void {
		$declaration = <<<NUT
			unwrap = ^s: Result<String, Boolean> => Result<Integer, Boolean> :: s!->length;
		NUT;
		$result = $this->executeCodeSnippet("unwrap('ok');", valueDeclarations: $declaration);
		$this->assertEquals("2", $result);
	}

	public function testEarlyReturnUnhappyOnError(): void {
		$declaration = <<<NUT
			unwrap = ^s: Result<String, Boolean> => Result<Integer, Boolean> :: s!->length;
		NUT;
		$result = $this->executeCodeSnippet("unwrap(@false);", valueDeclarations: $declaration);
		$this->assertEquals("@false", $result);
	}

	public function testEarlyReturnUnhappyWithOptionalReturn(): void {
		$declaration = <<<NUT
			unwrap = ^s: ?String => ?Integer :: s!->length;
		NUT;
		$result = $this->executeCodeSnippet("unwrap('hello');", valueDeclarations: $declaration);
		$this->assertEquals("5", $result);
	}

	public function testEarlyReturnUnhappyOnEmpty(): void {
		$declaration = <<<NUT
			unwrap = ^s: ?String => ?Integer :: s!->length;
		NUT;
		$result = $this->executeCodeSnippet("unwrap(empty);", valueDeclarations: $declaration);
		$this->assertEquals("empty", $result);
	}
}
