<?php

namespace Walnut\Lang\Test\NativeCode\Map;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class ValuesWithoutKeyTest extends CodeExecutionTestHelper {

	public function testValuesWithoutKeyEmpty(): void {
		$result = $this->executeCodeSnippet("[:]->valuesWithoutKey('r');");
		$this->assertEquals("@MapItemNotFound![key: 'r']", $result);
	}

	public function testValuesWithoutKeyNotFound(): void {
		$result = $this->executeCodeSnippet("[a: 1, b: 2, c: 5, d: 10, e: 5]->valuesWithoutKey('r');");
		$this->assertEquals("@MapItemNotFound![key: 'r']", $result);
	}

	public function testValuesWithoutKeyNonEmpty(): void {
		$result = $this->executeCodeSnippet("[a: 1, b: 2, c: 5]->valuesWithoutKey('b');");
		$this->assertEquals("[a: 1, c: 5]", $result);
	}

	public function testValuesWithoutKeyInvalidParameterValue(): void {
		$this->executeErrorCodeSnippet("Invalid parameter type",
			"[a: 'a', b: 1, c: 2]->valuesWithoutKey(15)");
	}
}