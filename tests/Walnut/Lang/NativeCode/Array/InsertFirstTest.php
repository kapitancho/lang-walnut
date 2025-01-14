<?php

namespace Walnut\Lang\NativeCode\Array;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class InsertFirstTest extends CodeExecutionTestHelper {

	public function testInsertFirstEmpty(): void {
		$result = $this->executeCodeSnippet("[]->insertFirst(1);");
		$this->assertEquals("[1]", $result);
	}

	public function testInsertFirstNonEmpty(): void {
		$result = $this->executeCodeSnippet("[1, 2]->insertFirst('a');");
		$this->assertEquals("['a', 1, 2]", $result);
	}
}