<?php

namespace Walnut\Lang\Test\NativeCode\Array;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class MaxTest extends CodeExecutionTestHelper {

	public function testMaxEmpty(): void {
		$this->executeErrorCodeSnippet('Invalid target type', "[]->max;");
	}

	public function testMaxNonEmpty(): void {
		$result = $this->executeCodeSnippet("[1, 2]->max;");
		$this->assertEquals("2", $result);
	}

	public function testMaxNonEmptyReal(): void {
		$result = $this->executeCodeSnippet("[1, 3.14]->max;");
		$this->assertEquals("3.14", $result);
	}

	public function testMaxInvalidType(): void {
		$this->executeErrorCodeSnippet('Invalid target type', "['hello','world', 'hi', 'hello']->max;");
	}
}