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

	public function testKeysSetKeyType(): void {
		$result = $this->executeCodeSnippet(
			"fn[a: 1, b: 2];",
			valueDeclarations: "fn = ^m: Map<String<1>:Integer> => Set<String<1>> :: m->keysSet;"
		);
		$this->assertEquals("['a'; 'b']", $result);
	}

}