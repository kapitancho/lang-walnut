<?php

namespace Walnut\Lang\Test\NativeCode\File;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class ContentTest extends CodeExecutionTestHelper {

	public function testContentWithInvalidTargetType(): void {
		$this->executeErrorCodeSnippet(
			'Cannot call method',
			"'not a file'->content;",
			typeDeclarations: "File := \$[path: String];"
		);
	}

	public function testContentThrowsExceptionWithWrongType(): void {
		$this->executeErrorCodeSnippet(
			'Cannot call method',
			"123->content;",
			typeDeclarations: "File := \$[path: String];"
		);
	}

}
