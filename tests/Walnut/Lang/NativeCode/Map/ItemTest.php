<?php

namespace Walnut\Lang\NativeCode\Map;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class ItemTest extends CodeExecutionTestHelper {

	public function testItemEmpty(): void {
		$this->executeErrorCodeSnippet(
			"No property exists that matches the type",
			"getItem('r');",
			"getItem = ^s: String :: [:]->item(s);"
		);
	}

	public function testItemNonEmpty(): void {
		$result = $this->executeCodeSnippet("[a: 1, b: 2]->item('b');");
		$this->assertEquals("2", $result);
	}

	public function testItemNonEmptyIndexOutOfRange(): void {
		$result = $this->executeCodeSnippet("getItem('r');", "getItem = ^s: String :: [a: 1, b: 2, c: 5, d: 10, e: 5]->item(s);");
		$this->assertEquals("@MapItemNotFound[key: 'r']", $result);
	}

	public function testItemInvalidParameterValue(): void {
		$this->executeErrorCodeSnippet("Invalid parameter type",
			"[a: 'a', b: 1, c: 2]->item(5)");
	}
}