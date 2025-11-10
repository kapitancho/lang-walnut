<?php

namespace Walnut\Lang\Test\NativeCode\File;

final class AppendContentTest extends FileAccessHelper {

	public function testAppendContentAdd(): void {
		$this->executeCodeSnippet(
			"{File[path: '$this->tempFilePath']}->appendContent('hello');",
			typeDeclarations: "File := \$[path: String]; CannotWriteFile := \$[file: File];			"
		);
		$this->assertEquals("hello", file_get_contents($this->tempFilePath));
	}

	public function testAppendContentAppend(): void {
		file_put_contents($this->tempFilePath, "hi!");
		$this->executeCodeSnippet(
			"{File[path: '$this->tempFilePath']}->appendContent('hello');",
			typeDeclarations: "File := \$[path: String]; CannotWriteFile := \$[file: File];			"
		);
		$this->assertEquals("hi!hello", file_get_contents($this->tempFilePath));
	}

	public function testAppendContentCannotWriteFile(): void {
		file_put_contents($this->tempFilePath, "hi!");
		chmod($this->tempFilePath, 0444); // Read-only
		$result = $this->executeCodeSnippet(
			"{File[path: '$this->tempFilePath']}->appendContent('hello');",
			typeDeclarations: "File := \$[path: String]; CannotWriteFile := \$[file: File];			"
		);
		$this->assertStringContainsString("@CannotWriteFile", $result);
	}

	public function testContentInvalidParameterType(): void {
		$this->executeErrorCodeSnippet(
			'Invalid parameter type: Integer[42]',
			"{File[path: '$this->tempFilePath']}->appendContent(42);",
			typeDeclarations: "File := \$[path: String]; CannotWriteFile := \$[file: File];			"
		);
	}

}
