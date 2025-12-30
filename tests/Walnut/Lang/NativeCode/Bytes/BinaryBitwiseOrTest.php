<?php

namespace Walnut\Lang\Test\NativeCode\Bytes;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class BinaryBitwiseOrTest extends CodeExecutionTestHelper {

	public function testBinaryBitwiseOr(): void {
		$result = $this->executeCodeSnippet('"A" | "B";');
		$this->assertEquals('"C"', $result);
	}

	public function testBinaryBitwiseOrMultipleBytes(): void {
		$result = $this->executeCodeSnippet('"AB" | "BC";');
		$this->assertEquals('"CC"', $result);
	}

	public function testBinaryBitwiseOrInvalidParameter(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', '"A" | 5;');
	}

	public function testBinaryBitwiseOrLengthMismatch(): void {
		$result = $this->executeCodeSnippet('"E" | "BC";');
		$this->assertEquals('"BG"', $result);
	}

	public function testBinaryBitwiseOrLengthMismatchReversed(): void {
		$result = $this->executeCodeSnippet('"BC" | "E";');
		$this->assertEquals('"BG"', $result);
	}

	public function testBinaryBitwiseOrSpecialChars(): void {
		$result = $this->executeCodeSnippet('"\00\00\00\00" | "\\\\\\n\\t\\``";');
		$this->assertEquals('"\\\\\n\t\``"', $result);
	}

}
