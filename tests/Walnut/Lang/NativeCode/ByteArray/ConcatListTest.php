<?php

namespace Walnut\Lang\Test\NativeCode\ByteArray;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class ConcatListTest extends CodeExecutionTestHelper {

	public function testConcatList(): void {
		$result = $this->executeCodeSnippet('"hello "->concatList["world", "!"];');
		$this->assertEquals('"hello world!"', $result);
	}

	public function testConcatListReturnType(): void {
		$result = $this->executeCodeSnippet(
			'cl[str: "hello ", arr: ["world", "!"]];',
			valueDeclarations: '
				cl = ^[str: ByteArray<4..7>, arr: Array<ByteArray<1..5>, 1..3>] => ByteArray<5..22> ::
					#str->concatList(#arr);
			'
		);
		$this->assertEquals('"hello world!"', $result);
	}

	public function testConcatListInvalidArrayParameter(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', '"hello"->concatList[15, "World"];');
	}

	public function testConcatListInvalidParameter(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', '"hello"->concatList(23);');
	}

}
