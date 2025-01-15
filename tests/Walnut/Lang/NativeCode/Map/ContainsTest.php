<?php

namespace Walnut\Lang\NativeCode\Map;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class ContainsTest extends CodeExecutionTestHelper {

	public function testContainsEmpty(): void {
		$result = $this->executeCodeSnippet("[:]->contains(5);");
		$this->assertEquals("false", $result);
	}

	public function testContainsNonEmptyFalse(): void {
		$result = $this->executeCodeSnippet("[a: 1, b: 2, c: 5, d: 10, e: 5]->contains(7);");
		$this->assertEquals("false", $result);
	}

	public function testContainsNonEmptyYes(): void {
		$result = $this->executeCodeSnippet("[a: 1, b: 2, c: 5, d: 10, e: 5]->contains(5);");
		$this->assertEquals("true", $result);
	}
}