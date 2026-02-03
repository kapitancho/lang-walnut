<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Bytes;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class ConcatListTest extends CodeExecutionTestHelper {

	public function testConcatList(): void {
		$result = $this->executeCodeSnippet('"hello "->concatList["world", "!"];');
		$this->assertEquals('"hello world!"', $result);
	}

	public function testConcatListReturnType(): void {
		$result = $this->executeCodeSnippet(
			'cl[str: "hello ", arr: ["world", "!"]];',
			valueDeclarations: '
				cl = ^[str: Bytes<4..7>, arr: Array<Bytes<1..5>, 1..3>] => Bytes<5..22> ::
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
