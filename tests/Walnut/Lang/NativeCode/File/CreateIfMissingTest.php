<?php

namespace Walnut\Lang\Test\NativeCode\File;

final class CreateIfMissingTest extends FileAccessHelper {

	public function testCreateIfMissingAdd(): void {
		$this->executeCodeSnippet(
			"{File[path: '$this->tempFilePath']}->createIfMissing('hello');",
			typeDeclarations: "File := \$[path: String]; CannotWriteFile := \$[file: File];			"
		);
		$this->assertEquals("hello", file_get_contents($this->tempFilePath));
	}

	public function testCreateIfMissingAppend(): void {
		file_put_contents($this->tempFilePath, "hi!");
		$this->executeCodeSnippet(
			"{File[path: '$this->tempFilePath']}->createIfMissing('hello');",
			typeDeclarations: "File := \$[path: String]; CannotWriteFile := \$[file: File];			"
		);
		$this->assertEquals("hi!", file_get_contents($this->tempFilePath));
	}

	public function testCreateIfMissingCannotWriteFile(): void {
		// make the directory read-only to prevent file creation inside it
		chmod($this->tempDirPath, 0555); // Read and execute permissions only
		$result = $this->executeCodeSnippet(
			"{File[path: '$this->tempFilePath']}->createIfMissing('hello');",
			typeDeclarations: "File := \$[path: String]; CannotWriteFile := \$[file: File];			"
		);
		$this->assertStringContainsString("@CannotWriteFile", $result);
	}

	public function testContentInvalidParameterType(): void {
		$this->executeErrorCodeSnippet(
			'Invalid parameter type: Integer[42]',
			"{File[path: '$this->tempFilePath']}->createIfMissing(42);",
			typeDeclarations: "File := \$[path: String]; CannotWriteFile := \$[file: File];			"
		);
	}

}
