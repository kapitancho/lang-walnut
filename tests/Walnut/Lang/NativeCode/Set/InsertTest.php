<?php

namespace Walnut\Lang\Test\NativeCode\Set;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class InsertTest extends CodeExecutionTestHelper {

	public function testInsertEmpty(): void {
		$result = $this->executeCodeSnippet("[;]->insert(1);");
		$this->assertEquals("[1;]", $result);
	}

	public function testInsertNonEmptyNew(): void {
		$result = $this->executeCodeSnippet("[1; 2]->insert('a');");
		$this->assertEquals("[1; 2; 'a']", $result);
	}

	public function testInsertNonEmptyExisting(): void {
		$result = $this->executeCodeSnippet("[1; 2]->insert(1);");
		$this->assertEquals("[1; 2]", $result);
	}
}