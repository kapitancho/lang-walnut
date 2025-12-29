<?php

namespace Walnut\Lang\Test\NativeCode\Bytes;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class UnaryBitwiseNotTest extends CodeExecutionTestHelper {

	public function testUnaryBitwiseEmpty(): void {
		$result = $this->executeCodeSnippet('~"";');
		$this->assertEquals('""', $result);
	}

	public function testUnaryBitwiseNot(): void {
		$result = $this->executeCodeSnippet('~"A";');
		$this->assertEquals('"' . chr(0xBE) . '"', $result);
	}

	public function testUnaryBitwiseNotMultipleBytes(): void {
		$result = $this->executeCodeSnippet('~"AB";');
		$this->assertEquals('"' . chr(0xBE) . chr(0xBD) . '"', $result);
	}

	public function testUnaryBitwiseNotDoubleInversion(): void {
		$result = $this->executeCodeSnippet('~~"AB";');
		$this->assertEquals('"AB"', $result);
	}

}
