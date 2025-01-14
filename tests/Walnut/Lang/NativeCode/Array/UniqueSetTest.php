<?php

namespace Walnut\Lang\Test\NativeCode\Array;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class UniqueSetTest extends CodeExecutionTestHelper {

	public function testUniqueSetEmpty(): void {
		$result = $this->executeCodeSnippet("[]->uniqueSet;");
		$this->assertEquals("[;]", $result);
	}

	public function testUniqueSetNonEmptyNumbers(): void {
		$result = $this->executeCodeSnippet("[1, 2, 5.3, 2]->uniqueSet;");
		$this->assertEquals("[1; 2; 5.3]", $result);
	}

	public function testUniqueSetNonEmptyStrings(): void {
		$result = $this->executeCodeSnippet("['hello','world', 'hi', 'hello']->uniqueSet;");
		$this->assertEquals("['hello'; 'world'; 'hi']", $result);
	}

	public function testUniqueSetInvalidType(): void {
		$this->executeErrorCodeSnippet('Invalid target type', "[12, 'hello','world', 'hi', 'hello']->uniqueSet;");
	}
}