<?php

namespace Walnut\Lang\Test\NativeCode\File;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class AppendContentTest extends CodeExecutionTestHelper {

	public function testAppendContentWithInvalidTargetType(): void {
		$this->executeErrorCodeSnippet(
			'Cannot call method',
			"'not a file'->appendContent['new content'];",
			typeDeclarations: "File := \$[path: String];"
		);
	}

	public function testAppendContentWithInvalidParameter(): void {
		$this->executeErrorCodeSnippet(
			'Cannot call method',
			"123->appendContent[42];",
			typeDeclarations: "File := \$[path: String];"
		);
	}

}
