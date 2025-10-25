<?php

namespace Walnut\Lang\Test\NativeCode\Map;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class KeysSetTest extends CodeExecutionTestHelper {

	public function testKeysSetEmpty(): void {
		$result = $this->executeCodeSnippet("[:]->keysSet;");
		$this->assertEquals("[;]", $result);
	}

	public function testKeysSetNonEmpty(): void {
		$result = $this->executeCodeSnippet("[a: 1, b: 2, c: 5.3, d: 2]->keysSet;");
		$this->assertEquals("['a'; 'b'; 'c'; 'd']", $result);
	}

}