<?php

namespace Walnut\Lang\Test\NativeCode\RegExp;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class MatchStringTest extends CodeExecutionTestHelper {

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
