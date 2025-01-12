<?php

namespace Walnut\Lang\NativeCode\String;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class MatchesRegexpTest extends CodeExecutionTestHelper {

	public function testMatchesRegexpYes(): void {
		$result = $this->executeCodeSnippet("'hello'->matchesRegexp('\w+');");
		$this->assertEquals('true', $result);
	}

	public function testMatchesRegexpNo(): void {
		$result = $this->executeCodeSnippet("'hello'->matchesRegexp('\d+');");
		$this->assertEquals('false', $result);
	}

	public function testMatchesRegexpInvalidParameter(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "'hello'->matchesRegexp(23);");
	}

}