<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Bytes;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class BinaryBitwiseAndTest extends CodeExecutionTestHelper {

	public function testBinaryBitwiseAnd(): void {
		$result = $this->executeCodeSnippet('"W" & "G";');
		$this->assertEquals('"G"', $result);
	}

	public function testBinaryBitwiseAndMultipleBytes(): void {
		$result = $this->executeCodeSnippet('"WX" & "GH";');
		$this->assertEquals('"GH"', $result);
	}

	public function testBinaryBitwiseAndInvalidParameter(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', '"A" & 5;');
	}

	public function testBinaryBitwiseAndLengthMismatch(): void {
		$result = $this->executeCodeSnippet('"W" & "GH";');
		$this->assertEquals('"\00@"', $result);
	}

	public function testBinaryBitwiseAndLengthMismatchReversed(): void {
		$result = $this->executeCodeSnippet('"GH" & "W";');
		$this->assertEquals('"\00@"', $result);
	}

}
