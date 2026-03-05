<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\File;

final class ReplaceContentTest extends FileAccessHelper {

	public function testReplaceContentAddString(): void {
		$result = $this->executeCodeSnippet(
			"{File[path: '$this->tempFilePath']}->replaceContent('hello');",
			typeDeclarations: "File := \$[path: String]; CannotWriteFile := \$[file: File];			"
		);
		$this->assertEquals("'hello'", $result);
		$this->assertEquals("hello", file_get_contents($this->tempFilePath));
	}

	public function testReplaceContentAddBytes(): void {
		$result = $this->executeCodeSnippet(
			"{File[path: '$this->tempFilePath']}->replaceContent(\"hello\");",
			typeDeclarations: "File := \$[path: String]; CannotWriteFile := \$[file: File];			"
		);
		$this->assertEquals('"hello"', $result);
		$this->assertEquals("hello", file_get_contents($this->tempFilePath));
	}

	public function testReplaceContentAppendString(): void {
		file_put_contents($this->tempFilePath, "hi!");
		$result = $this->executeCodeSnippet(
			"{File[path: '$this->tempFilePath']}->replaceContent('hello');",
			typeDeclarations: "File := \$[path: String]; CannotWriteFile := \$[file: File];			"
		);
		$this->assertEquals("'hello'", $result);
		$this->assertEquals("hello", file_get_contents($this->tempFilePath));
	}

	public function testReplaceContentAppendBytes(): void {
		file_put_contents($this->tempFilePath, "hi!");
		$result = $this->executeCodeSnippet(
			"{File[path: '$this->tempFilePath']}->replaceContent(\"hello\");",
			typeDeclarations: "File := \$[path: String]; CannotWriteFile := \$[file: File];			"
		);
		$this->assertEquals('"hello"', $result);
		$this->assertEquals("hello", file_get_contents($this->tempFilePath));
	}

	public function testReplaceContentCannotWriteFile(): void {
		file_put_contents($this->tempFilePath, "hi!");
		chmod($this->tempFilePath, 0444); // Read-only
		$result = $this->executeCodeSnippet(
			"{File[path: '$this->tempFilePath']}->replaceContent('hello');",
			typeDeclarations: "File := \$[path: String]; CannotWriteFile := \$[file: File];			"
		);
		$this->assertStringContainsString("@CannotWriteFile", $result);
	}

	public function testContentInvalidParameterType(): void {
		$this->executeErrorCodeSnippet(
			'Invalid parameter type: Integer[42]',
			"{File[path: '$this->tempFilePath']}->replaceContent(42);",
			typeDeclarations: "File := \$[path: String]; CannotWriteFile := \$[file: File];			"
		);
	}

}
