<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Bytes;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class ConcatTest extends CodeExecutionTestHelper {

	public function testConcat(): void {
		$result = $this->executeCodeSnippet('"hello "->concat("world");');
		$this->assertEquals('"hello world"', $result);
	}

	public function testConcatUnionType(): void {
		$result = $this->executeCodeSnippet(
			'c("world");',
			valueDeclarations: 'c = ^b: Bytes<5>|Bytes<10> => Bytes :: "hello "->concat(b);'
		);
		$this->assertEquals('"hello world"', $result);
	}

	public function testConcatInvalidParameter(): void {
		$this->executeErrorCodeSnippet('Parameter type Integer[23] is not a subtype of Bytes', '"hello"->concat(23);');
	}

}
