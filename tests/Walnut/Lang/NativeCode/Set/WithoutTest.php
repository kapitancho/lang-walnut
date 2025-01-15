<?php

namespace Walnut\Lang\NativeCode\Set;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class WithoutTest extends CodeExecutionTestHelper {

	public function testWithoutEmpty(): void {
		$result = $this->executeCodeSnippet("[;]->without(3);");
		$this->assertEquals("[;]", $result);
	}

	public function testWithoutNotFound(): void {
		$result = $this->executeCodeSnippet("[1; 2; 5; 10; 5]->without(3);");
		$this->assertEquals("[1; 2; 5; 10]", $result);
	}

	public function testWithoutNonEmpty(): void {
		$result = $this->executeCodeSnippet("[1; 2; 5; 10; 5]->without(5);");
		$this->assertEquals("[1; 2; 10]", $result);
	}
}