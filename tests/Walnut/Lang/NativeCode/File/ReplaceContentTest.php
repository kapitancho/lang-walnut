<?php

namespace Walnut\Lang\Test\NativeCode\File;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class ReplaceContentTest extends CodeExecutionTestHelper {

	public function testReplaceContentWithInvalidTargetType(): void {
		$this->executeErrorCodeSnippet(
			'Cannot call method',
			"'not a file'->replaceContent['new content'];",
			typeDeclarations: "File := \$[path: String];"
		);
	}

	public function testReplaceContentWithInvalidParameter(): void {
		$this->executeErrorCodeSnippet(
			'Cannot call method',
			"123->replaceContent[42];",
			typeDeclarations: "File := \$[path: String];"
		);
	}

}
