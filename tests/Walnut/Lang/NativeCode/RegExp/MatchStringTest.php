<?php

namespace Walnut\Lang\Test\NativeCode\RegExp;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class MatchStringTest extends CodeExecutionTestHelper {

	public function testMatchStringMatch(): void {
		$result = $this->executeCodeSnippet(
			"{RegExp('/^hello ([a-z]+)/')}->matchString('hello world');",
			typeDeclarations: 'RegExp := $String; InvalidRegExp := [expression: String];'
		);
		$this->assertEquals("RegExpMatch![\n	match: 'hello world',\n	groups: ['world']\n]", $result);
	}

	public function testMatchStringNoMatch(): void {
		$result = $this->executeCodeSnippet(
			"{RegExp('/^hello ([a-z]+)/')}->matchString('hello 42');",
			typeDeclarations: 'RegExp := $String; InvalidRegExp := [expression: String];'
		);
		$this->assertEquals("@NoRegExpMatch", $result);
	}

	public function testMatchStringInvalidRegExp(): void {
		$result = $this->executeCodeSnippet(
			"RegExp('^he')?->matchString('hello 42');",
			typeDeclarations: 'RegExp := $String; InvalidRegExp := [expression: String];'
		);
		$this->assertEquals("@InvalidRegExp![expression: '^he']", $result);
	}

	public function testMatchStringWithInvalidTargetType(): void {
		$this->executeErrorCodeSnippet(
			'Cannot call method',
			"'not a regexp'->matchString['test'];",
			typeDeclarations: "RegExp := \$[pattern: String];"
		);
	}

	public function testMatchStringWithInvalidParameter(): void {
		$this->executeErrorCodeSnippet(
			'Cannot call method',
			"123->matchString['test'];",
			typeDeclarations: "RegExp := \$[pattern: String];"
		);
	}

}
