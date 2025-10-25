<?php

namespace Walnut\Lang\Test\NativeCode\Set;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class ContainsTest extends CodeExecutionTestHelper {

	public function testContainsEmpty(): void {
		$result = $this->executeCodeSnippet("[;]->contains(5);");
		$this->assertEquals("false", $result);
	}

	public function testContainsNonEmptyFalse(): void {
		$result = $this->executeCodeSnippet("[1; 2; 5; 10; 5]->contains(7);");
		$this->assertEquals("false", $result);
	}

	public function testContainsNonEmptyYes(): void {
		$result = $this->executeCodeSnippet("[1; 2; 5; 10; 5]->contains(5);");
		$this->assertEquals("true", $result);
	}
}