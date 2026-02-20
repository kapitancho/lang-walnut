<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\String;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class MatchesRegexpTest extends CodeExecutionTestHelper {

	public function testMatchesRegexpYes(): void {
		$result = $this->executeCodeSnippet("'hello'->matchesRegexp('/\w+/'->asRegExp);");
		$this->assertEquals('true', $result);
	}

	public function testMatchesRegexpNo(): void {
		$result = $this->executeCodeSnippet("'hello'->matchesRegexp('/\d+/'->asRegExp);");
		$this->assertEquals('false', $result);
	}

	public function testMatchesRegexpInvalidParameter(): void {
		$this->executeErrorCodeSnippet('The parameter type Integer[23] is not a subtype of RegExp', "'hello'->matchesRegexp(23);");
	}

}