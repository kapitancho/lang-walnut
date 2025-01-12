<?php

namespace Walnut\Lang\NativeCode\String;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class StartsWithTest extends CodeExecutionTestHelper {

	public function testStartsWithYes(): void {
		$result = $this->executeCodeSnippet("'hello'->startsWith('he');");
		$this->assertEquals('true', $result);
	}

	public function testStartsWithNo(): void {
		$result = $this->executeCodeSnippet("'hello'->startsWith('llo');");
		$this->assertEquals('false', $result);
	}

	public function testStartsWithInvalidParameter(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "'hello'->startsWith(23);");
	}

}