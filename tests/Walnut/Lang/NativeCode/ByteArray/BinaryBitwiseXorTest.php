<?php

namespace Walnut\Lang\Test\NativeCode\ByteArray;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class BinaryBitwiseXorTest extends CodeExecutionTestHelper {

	public function testBinaryBitwiseXorEqual(): void {
		$result = $this->executeCodeSnippet('"A" ^ "A";');
		$this->assertEquals('"' . chr(0) . '"', $result);
	}

	public function testBinaryBitwiseXor(): void {
		$result = $this->executeCodeSnippet('"A" ^ "C";');
		$this->assertEquals('"' . chr(2) . '"', $result);
	}

	public function testBinaryBitwiseXorMultipleBytes(): void {
		$result = $this->executeCodeSnippet('"AB" ^ "CD";');
		$this->assertEquals('"' . chr(2) . chr(6) . '"', $result);
	}

	public function testBinaryBitwiseXorSelf(): void {
		$result = $this->executeCodeSnippet('"AB" ^ "AB";');
		$this->assertEquals('"' . chr(0) . chr(0) . '"', $result);
	}

	public function testBinaryBitwiseXorInvalidParameter(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', '"A" ^ 5;');
	}

	public function testBinaryBitwiseXorLengthMismatch(): void {
		$result = $this->executeCodeSnippet('"A" ^ "BC";');
		$this->assertEquals('"B' . chr(2) . '"', $result);
	}

	public function testBinaryBitwiseXorLengthMismatchReversed(): void {
		$result = $this->executeCodeSnippet('"BC" ^ "A";');
		$this->assertEquals('"B' . chr(2) . '"', $result);
	}

}
