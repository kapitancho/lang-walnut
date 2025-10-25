<?php

namespace Walnut\Lang\Test\NativeCode\Array;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class InsertLastTest extends CodeExecutionTestHelper {

	public function testInsertLastEmpty(): void {
		$result = $this->executeCodeSnippet("[]->insertLast(1);");
		$this->assertEquals("[1]", $result);
	}

	public function testInsertLastNonEmpty(): void {
		$result = $this->executeCodeSnippet("[1, 2]->insertLast('a');");
		$this->assertEquals("[1, 2, 'a']", $result);
	}
}