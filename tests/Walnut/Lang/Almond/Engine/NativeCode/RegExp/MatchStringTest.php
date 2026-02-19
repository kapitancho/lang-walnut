<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\RegExp;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class MatchStringTest extends CodeExecutionTestHelper {

	public function testMatchStringMatch(): void {
		$result = $this->executeCodeSnippet(
			"RegExp('/^hello ([a-z]+)/')?->matchString('hello world');"
		);
		$this->assertEquals("RegExpMatch![\n	match: 'hello world',\n	groups: ['world']\n]", $result);
	}

	public function testMatchStringNoMatch(): void {
		$result = $this->executeCodeSnippet(
			"RegExp('/^hello ([a-z]+)/')?->matchString('hello 42');",
		);
		$this->assertEquals("@NoRegExpMatch", $result);
	}

	public function testMatchStringInvalidRegExp(): void {
		$result = $this->executeCodeSnippet(
			"RegExp('^he')?->matchString('hello 42');",
		);
		$this->assertEquals("@InvalidRegExp![expression: '^he']", $result);
	}

	public function testMatchStringWithInvalidParameter(): void {
		$this->executeErrorCodeSnippet(
			"Invalid parameter type: [String['test']]",
			"RegExp('/^hello ([a-z]+)/')?->matchString['test'];",
		);
	}

}
