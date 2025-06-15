<?php

namespace Walnut\Lang\NativeCode\Any;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class BinaryEqualTest extends CodeExecutionTestHelper {

	public function testBinaryEqualMap(): void {
		$result = $this->executeCodeSnippet("[a: 1, b: 2, c: 5] == [b: 2, c: 5, a: 1]");
		$this->assertEquals("true", $result);
	}

	public function testBinaryEqualSetTrue(): void {
		$result = $this->executeCodeSnippet("[1; 2; 5] == [2; 5; 1]");
		$this->assertEquals("true", $result);
	}

	public function testBinaryEqualSetFalse(): void {
		$result = $this->executeCodeSnippet("[1; 2; 5] == [2; 5; 4]");
		$this->assertEquals("false", $result);
	}

	public function testBinaryEqualSetFalseDifferentType(): void {
		$result = $this->executeCodeSnippet("[1; 2; 5] == [1, 2, 5]");
		$this->assertEquals("false", $result);
	}

	public function testBinaryEqualArrayFalse(): void {
		$result = $this->executeCodeSnippet("[1, 2, 5] == [2, 5, 1]");
		$this->assertEquals("false", $result);
	}

	public function testBinaryEqualArrayTrue(): void {
		$result = $this->executeCodeSnippet("[1, 2, 5] == [1, 2, 5]");
		$this->assertEquals("true", $result);
	}

	public function testBinaryEqualOpenTrue(): void {
		$result = $this->executeCodeSnippet(
			"{MyOpen[1, 2, 5]} == MyOpen[1, 2, 5]",
			"MyOpen := #Array<Integer>;"
		);
		$this->assertEquals("true", $result);
	}

	public function testBinaryEqualOpenFalse(): void {
		$result = $this->executeCodeSnippet(
			"{MyOpen[1, 2, 5]} == MyOpen[1, 2, 6]",
			"MyOpen := #Array<Integer>;"
		);
		$this->assertEquals("false", $result);
	}

	public function testBinaryEqualOpenDifferent(): void {
		$result = $this->executeCodeSnippet(
			"{MyOpen[1, 2, 5]} == MyOtherOpen[1, 2, 5]",
			"
				MyOpen := #Array<Integer>;
				MyOtherOpen := #Array<Integer>;
			"
		);
		$this->assertEquals("false", $result);
	}

}