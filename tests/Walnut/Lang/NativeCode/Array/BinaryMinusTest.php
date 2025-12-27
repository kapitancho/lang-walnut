<?php

namespace Walnut\Lang\NativeCode\Array;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class BinaryMinusTest extends CodeExecutionTestHelper {

	public function testWithoutAllEmpty(): void {
		$result = $this->executeCodeSnippet("[] - 3;");
		$this->assertEquals("[]", $result);
	}

	public function testWithoutAllNotFound(): void {
		$result = $this->executeCodeSnippet("[1, 2, 5, 10, 5] - 3;");
		$this->assertEquals("[1, 2, 5, 10, 5]", $result);
	}

	public function testWithoutAllNonEmpty(): void {
		$result = $this->executeCodeSnippet("[1, 2, 5, 10, 5] - 5;");
		$this->assertEquals("[1, 2, 10]", $result);
	}

	public function testWithoutAllReturnType(): void {
		$result = $this->executeCodeSnippet(
			"rem[1, 2, 5, 10, 5];",
			valueDeclarations: "rem = ^arr: Array<Integer, 3..7> => Array<Integer, ..7> :: arr - 5;"
		);
		$this->assertEquals("[1, 2, 10]", $result);
	}
}