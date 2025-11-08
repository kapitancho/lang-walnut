<?php

namespace Walnut\Lang\Test\NativeCode\File;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class CreateIfMissingTest extends CodeExecutionTestHelper {

	public function testCreateIfMissingWithInvalidTargetType(): void {
		$this->executeErrorCodeSnippet(
			'Cannot call method',
			"'not a file'->createIfMissing['initial content'];",
			typeDeclarations: "File := \$[path: String];"
		);
	}

	public function testCreateIfMissingWithInvalidParameter(): void {
		$this->executeErrorCodeSnippet(
			'Cannot call method',
			"123->createIfMissing[42];",
			typeDeclarations: "File := \$[path: String];"
		);
	}

}
