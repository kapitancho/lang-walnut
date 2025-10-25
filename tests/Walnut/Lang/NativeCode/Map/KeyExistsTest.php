<?php

namespace Walnut\Lang\Test\NativeCode\Map;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class KeyExistsTest extends CodeExecutionTestHelper {

	public function testKeyExistsEmpty(): void {
		$result = $this->executeCodeSnippet("[:]->keyExists('a');");
		$this->assertEquals("false", $result);
	}

	public function testKeyExistsNonEmptyFalse(): void {
		$result = $this->executeCodeSnippet("[a: 1, b: 2, c: 5, d: 10, e: 5]->keyExists('r');");
		$this->assertEquals("false", $result);
	}

	public function testKeyExistsNonEmptyYes(): void {
		$result = $this->executeCodeSnippet("[a: 1, b: 2, c: 5, d: 10, e: 5]->keyExists('b');");
		$this->assertEquals("true", $result);
	}

	public function testKeyExistsInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "[a: 1, b: 'a']->keyExists(5);");
	}

}