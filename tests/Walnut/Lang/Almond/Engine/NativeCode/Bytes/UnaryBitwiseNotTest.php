<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Bytes;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class UnaryBitwiseNotTest extends CodeExecutionTestHelper {

	public function testUnaryBitwiseEmpty(): void {
		$result = $this->executeCodeSnippet('~"";');
		$this->assertEquals('""', $result);
	}

	public function testUnaryBitwiseNot(): void {
		$result = $this->executeCodeSnippet('~"A";');
		$this->assertEquals('"\BE"', $result);
	}

	public function testUnaryBitwiseNotMultipleBytes(): void {
		$result = $this->executeCodeSnippet('~"AB";');
		$this->assertEquals('"\BE\BD"', $result);
	}

	public function testUnaryBitwiseNotDoubleInversion(): void {
		$result = $this->executeCodeSnippet('~~"AB";');
		$this->assertEquals('"AB"', $result);
	}

}
