<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\File;

final class AppendContentTest extends FileAccessHelper {

	public function testAppendContentAddString(): void {
		$result = $this->executeCodeSnippet(
			"{File[path: '$this->tempFilePath']}->appendContent('hello');",
			typeDeclarations: "File := \$[path: String]; CannotWriteFile := \$[file: File];			"
		);
		$this->assertEquals("'hello'", $result);
		$this->assertEquals("hello", file_get_contents($this->tempFilePath));
	}

	public function testAppendContentAddBytes(): void {
		$result = $this->executeCodeSnippet(
			"{File[path: '$this->tempFilePath']}->appendContent(\"hello\");",
			typeDeclarations: "File := \$[path: String]; CannotWriteFile := \$[file: File];			"
		);
		$this->assertEquals('"hello"', $result);
		$this->assertEquals("hello", file_get_contents($this->tempFilePath));
	}

	public function testAppendContentAppendString(): void {
		file_put_contents($this->tempFilePath, "hi!");
		$result = $this->executeCodeSnippet(
			"{File[path: '$this->tempFilePath']}->appendContent('hello');",
			typeDeclarations: "File := \$[path: String]; CannotWriteFile := \$[file: File];			"
		);
		$this->assertEquals("'hello'", $result);
		$this->assertEquals("hi!hello", file_get_contents($this->tempFilePath));
	}

	public function testAppendContentAppendBytes(): void {
		file_put_contents($this->tempFilePath, "hi!");
		$result = $this->executeCodeSnippet(
			"{File[path: '$this->tempFilePath']}->appendContent(\"hello\");",
			typeDeclarations: "File := \$[path: String]; CannotWriteFile := \$[file: File];			"
		);
		$this->assertEquals('"hello"', $result);
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
