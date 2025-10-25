<?php

namespace Walnut\Lang\Test\NativeCode\Map;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class WithoutByKeyTest extends CodeExecutionTestHelper {

	public function testWithoutByKeyEmpty(): void {
		$result = $this->executeCodeSnippet("[:]->withoutByKey('r');");
		$this->assertEquals("@MapItemNotFound![key: 'r']", $result);
	}

	public function testWithoutByKeyNotFound(): void {
		$result = $this->executeCodeSnippet("[a: 1, b: 2, c: 5, d: 10, e: 5]->withoutByKey('r');");
		$this->assertEquals("@MapItemNotFound![key: 'r']", $result);
	}

	public function testWithoutByKeyNonEmpty(): void {
		$result = $this->executeCodeSnippet("[a: 1, b: 2, c: 5]->withoutByKey('b');");
		$this->assertEquals("[element: 2, map: [a: 1, c: 5]]", $result);
	}

	public function testWithoutByKeyInvalidParameterValue(): void {
		$this->executeErrorCodeSnippet("Invalid parameter type",
			"[a: 'a', b: 1, c: 2]->withoutByKey(15)");
	}
}