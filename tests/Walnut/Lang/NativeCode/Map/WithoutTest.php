<?php

namespace Walnut\Lang\NativeCode\Map;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class WithoutTest extends CodeExecutionTestHelper {

	public function testWithoutEmpty(): void {
		$result = $this->executeCodeSnippet("[:]->without('a');");
		$this->assertEquals("@ItemNotFound", $result);
	}

	public function testWithoutNotFound(): void {
		$result = $this->executeCodeSnippet("[a: 1, b: 2, c: 5, d: 10, e: 5]->without('r');");
		$this->assertEquals("@ItemNotFound", $result);
	}

	public function testWithoutNonEmpty(): void {
		$result = $this->executeCodeSnippet("[a: 1, b: 2, c: 5, d: 10, e: 5]->without(5);");
		$this->assertEquals("[a: 1, b: 2, d: 10, e: 5]", $result);
	}

}