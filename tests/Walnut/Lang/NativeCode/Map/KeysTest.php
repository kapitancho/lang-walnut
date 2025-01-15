<?php

namespace Walnut\Lang\NativeCode\Map;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class KeysTest extends CodeExecutionTestHelper {

	public function testKeysEmpty(): void {
		$result = $this->executeCodeSnippet("[:]->keys;");
		$this->assertEquals("[]", $result);
	}

	public function testKeysNonEmpty(): void {
		$result = $this->executeCodeSnippet("[a: 1, b: 2, c: 5.3, d: 2]->keys;");
		$this->assertEquals("['a', 'b', 'c', 'd']", $result);
	}

}