<?php

namespace Walnut\Lang\Test\NativeCode\Array;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class AppendWithTest extends CodeExecutionTestHelper {

	public function testAppendWithEmpty(): void {
		$result = $this->executeCodeSnippet("[]->appendWith([]);");
		$this->assertEquals("[]", $result);
	}

	public function testAppendWithNonEmpty(): void {
		$result = $this->executeCodeSnippet("[1, 2]->appendWith[3, 4];");
		$this->assertEquals("[1, 2, 3, 4]", $result);
	}

	public function testAppendWithInvalidType(): void {
		$this->executeErrorCodeSnippet('Invalid target type', "[1, 2]->appendWith(3);");
	}
}