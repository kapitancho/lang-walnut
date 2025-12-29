<?php

namespace Walnut\Lang\Test\NativeCode\Bytes;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class ConcatTest extends CodeExecutionTestHelper {

	public function testConcat(): void {
		$result = $this->executeCodeSnippet('"hello "->concat("world");');
		$this->assertEquals('"hello world"', $result);
	}

	public function testConcatAliasType(): void {
		$result = $this->executeCodeSnippet(
			'c("world");',
			valueDeclarations: 'c = ^b: Bytes<5>|Bytes<10> => Bytes :: "hello "->concat(b);'
		);
		$this->assertEquals('"hello world"', $result);
	}

	public function testConcatInvalidParameter(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', '"hello"->concat(23);');
	}

}
