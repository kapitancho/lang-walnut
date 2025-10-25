<?php

namespace Walnut\Lang\Test\NativeCode\Set;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class WithRemovedTest extends CodeExecutionTestHelper {

	public function testWithRemovedEmpty(): void {
		$result = $this->executeCodeSnippet("[;]->withRemoved(3);");
		$this->assertEquals("@ItemNotFound", $result);
	}

	public function testWithRemovedNotFound(): void {
		$result = $this->executeCodeSnippet("[1; 2; 5; 10; 5]->withRemoved(3);");
		$this->assertEquals("@ItemNotFound", $result);
	}

	public function testWithRemovedNonEmpty(): void {
		$result = $this->executeCodeSnippet("[1; 2; 5; 10; 5]->withRemoved(5);");
		$this->assertEquals("[1; 2; 10]", $result);
	}
}