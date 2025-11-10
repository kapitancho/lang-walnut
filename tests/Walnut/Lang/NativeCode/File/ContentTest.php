<?php

namespace Walnut\Lang\Test\NativeCode\File;

final class ContentTest extends FileAccessHelper {

	public function testContentOk(): void {
		file_put_contents($this->tempFilePath, "hi!");
		$result = $this->executeCodeSnippet(
			"{File[path: '$this->tempFilePath']}->content;",
			typeDeclarations: "File := \$[path: String]; CannotReadFile := \$[file: File];			"
		);
		$this->assertEquals("'hi!'", $result);
	}

	public function testContentCannotReadFile(): void {
		$result = $this->executeCodeSnippet(
			"{File[path: '$this->tempFilePath']}->content;",
			typeDeclarations: "File := \$[path: String]; CannotReadFile := \$[file: File];			"
		);
		$this->assertStringContainsString("@CannotReadFile", $result);
	}

	public function testContentInvalidParameterType(): void {
		$this->executeErrorCodeSnippet(
			'Invalid parameter type: Integer[42]',
			"{File[path: '$this->tempFilePath']}->content(42);",
			typeDeclarations: "File := \$[path: String]; CannotReadFile := \$[file: File];			"
		);
	}

}
