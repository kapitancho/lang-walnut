<?php

namespace Walnut\Lang\Test\NativeCode\Any;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class BinaryNotEqualTest extends CodeExecutionTestHelper {

	public function testBinaryNotEqualMap(): void {
		$result = $this->executeCodeSnippet("[a: 1, b: 2, c: 5] != [b: 2, c: 5, a: 1]");
		$this->assertEquals("false", $result);
	}

	public function testBinaryNotEqualSet(): void {
		$result = $this->executeCodeSnippet("[1; 2; 5] != [2; 5; 1]");
		$this->assertEquals("false", $result);
	}

	public function testBinaryNotEqualArrayFalse(): void {
		$result = $this->executeCodeSnippet("[1, 2, 5] != [2, 5, 1]");
		$this->assertEquals("true", $result);
	}

	public function testBinaryNotEqualArrayTrue(): void {
		$result = $this->executeCodeSnippet("[1, 2, 5] != [1, 2, 5]");
		$this->assertEquals("false", $result);
	}

}