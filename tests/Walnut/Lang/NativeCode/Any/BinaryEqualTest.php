<?php

namespace Walnut\Lang\NativeCode\Any;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class BinaryEqualTest extends CodeExecutionTestHelper {

	public function testBinaryEqualMap(): void {
		$result = $this->executeCodeSnippet("[a: 1, b: 2, c: 5] == [b: 2, c: 5, a: 1]");
		$this->assertEquals("true", $result);
	}

	public function testBinaryEqualSet(): void {
		$result = $this->executeCodeSnippet("[1; 2; 5] == [2; 5; 1]");
		$this->assertEquals("true", $result);
	}

	public function testBinaryEqualArrayFalse(): void {
		$result = $this->executeCodeSnippet("[1, 2, 5] == [2, 5, 1]");
		$this->assertEquals("false", $result);
	}

	public function testBinaryEqualArrayTrue(): void {
		$result = $this->executeCodeSnippet("[1, 2, 5] == [1, 2, 5]");
		$this->assertEquals("true", $result);
	}

}