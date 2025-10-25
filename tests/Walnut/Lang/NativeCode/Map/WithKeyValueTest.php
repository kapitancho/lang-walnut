<?php

namespace Walnut\Lang\Test\NativeCode\Map;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class WithKeyValueTest extends CodeExecutionTestHelper {

	public function testWithKeyValueEmpty(): void {
		$result = $this->executeCodeSnippet("[:]->withKeyValue[value: 1, key: 'a'];");
		$this->assertEquals("[a: 1]", $result);
	}

	public function testWithKeyValueNonEmptyAdd(): void {
		$result = $this->executeCodeSnippet("[a: 'a', b: 1, c: 2]->withKeyValue[value: 5, key: 'f'];");
		$this->assertEquals("[a: 'a', b: 1, c: 2, f: 5]", $result);
	}

	public function testWithKeyValueNonEmptyReplace(): void {
		$result = $this->executeCodeSnippet("[a: 'a', b: 1, c: 2]->withKeyValue[value: 5, key: 'b'];");
		$this->assertEquals("[a: 'a', b: 5, c: 2]", $result);
	}

	public function testWithKeyValueInvalidParameterValue(): void {
		$this->executeErrorCodeSnippet("Invalid parameter type",
			"[a: 'a', b: 1, c: 2]->withKeyValue[value: 'b']");
	}

	public function testWithKeyValueInvalidParameterType(): void {
		$this->executeErrorCodeSnippet("Invalid parameter type",
			"[a: 'a', b: 1, c: 2]->withKeyValue('b')");
	}
}