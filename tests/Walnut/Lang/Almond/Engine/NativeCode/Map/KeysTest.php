<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Map;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class KeysTest extends CodeExecutionTestHelper {

	public function testKeysEmpty(): void {
		$result = $this->executeCodeSnippet("[:]->keys;");
		$this->assertEquals("[]", $result);
	}

	public function testKeysNonEmpty(): void {
		$result = $this->executeCodeSnippet("[a: 1, b: 2, c: 5.3, d: 2]->keys;");
		$this->assertEquals("['a', 'b', 'c', 'd']", $result);
	}

	public function testKeysKeyType(): void {
		$result = $this->executeCodeSnippet(
			"fn[a: 1, b: 2];",
			valueDeclarations: "fn = ^m: Map<String<1>:Integer> => Array<String<1>> :: m->keys;"
		);
		$this->assertEquals("['a', 'b']", $result);
	}

}