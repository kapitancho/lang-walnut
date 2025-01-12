<?php

namespace Walnut\Lang\NativeCode\String;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class MatchAgainstPatternTest extends CodeExecutionTestHelper {

	public function testMatchAgainstPatternYes(): void {
		$result = $this->executeCodeSnippet("'hello/world'->matchAgainstPattern('hello/{who}');");
		$this->assertEquals("[who: 'world']", $result);
	}

	public function testMatchAgainstPatternFix(): void {
		$result = $this->executeCodeSnippet("'hello/world'->matchAgainstPattern('hello/world');");
		$this->assertEquals("[:]", $result);
	}

	public function testMatchAgainstPatternNo(): void {
		$result = $this->executeCodeSnippet("'hello/world'->matchAgainstPattern('goodbye/{who}');");
		$this->assertEquals('false', $result);
	}

	public function testMatchAgainstPatternInvalidParameter(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "'hello'->matchAgainstPattern(23);");
	}

}