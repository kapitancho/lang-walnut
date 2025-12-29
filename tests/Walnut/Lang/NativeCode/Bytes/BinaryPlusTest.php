<?php

namespace Walnut\Lang\Test\NativeCode\Bytes;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class BinaryPlusTest extends CodeExecutionTestHelper {

	public function testBinaryPlus(): void {
		$result = $this->executeCodeSnippet('"hello " + "world";');
		$this->assertEquals('"hello world"', $result);
	}

	public function testBinaryPlusInteger(): void {
		$result = $this->executeCodeSnippet('"hello" + 33;');
		$this->assertEquals('"hello!"', $result);
	}

	public function testBinaryPlusInvalidParameter(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', '"hello" + A;',
			"A := (); B := (); A ==> Bytes @ B :: B;");
	}

}
