<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Bytes;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class BinaryBitwiseXorTest extends CodeExecutionTestHelper {

	public function testBinaryBitwiseXorEqual(): void {
		$result = $this->executeCodeSnippet('"A" ^ "A";');
		$this->assertEquals('"\00"', $result);
	}

	public function testBinaryBitwiseXor(): void {
		$result = $this->executeCodeSnippet('"A" ^ "C";');
		$this->assertEquals('"\02"', $result);
	}

	public function testBinaryBitwiseXorMultipleBytes(): void {
		$result = $this->executeCodeSnippet('"AB" ^ "CD";');
		$this->assertEquals('"\02\06"', $result);
	}

	public function testBinaryBitwiseXorSelf(): void {
		$result = $this->executeCodeSnippet('"AB" ^ "AB";');
		$this->assertEquals('"\00\00"', $result);
	}

	public function testBinaryBitwiseXorInvalidParameter(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', '"A" ^ 5;');
	}

	public function testBinaryBitwiseXorLengthMismatch(): void {
		$result = $this->executeCodeSnippet('"A" ^ "BC";');
		$this->assertEquals('"B\02"', $result);
	}

	public function testBinaryBitwiseXorLengthMismatchReversed(): void {
		$result = $this->executeCodeSnippet('"BC" ^ "A";');
		$this->assertEquals('"B\02"', $result);
	}

}
