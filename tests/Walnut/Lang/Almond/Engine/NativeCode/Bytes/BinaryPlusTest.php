<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Bytes;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class BinaryPlusTest extends CodeExecutionTestHelper {

	public function testBinaryPlus(): void {
		$result = $this->executeCodeSnippet('"hello " + "world";');
		$this->assertEquals('"hello world"', $result);
	}

	public function testBinaryPlusUnionType(): void {
		$result = $this->executeCodeSnippet(
			'c("world");',
			valueDeclarations: 'c = ^b: Bytes<5>|Bytes<10> => Bytes :: "hello " + b;'
		);
		$this->assertEquals('"hello world"', $result);
	}

	public function testBinaryPlusInteger(): void {
		$result = $this->executeCodeSnippet('"hello" + 33;');
		$this->assertEquals('"hello!"', $result);
	}

	public function testBinaryPlusInvalidParameter(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', '"hello" + A;',
			"A := (); B := (); A ==> Bytes @ B :: @B;");
	}

}
