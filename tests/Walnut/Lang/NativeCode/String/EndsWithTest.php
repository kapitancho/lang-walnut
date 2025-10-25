<?php

namespace Walnut\Lang\Test\NativeCode\String;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class EndsWithTest extends CodeExecutionTestHelper {

	public function testEndsWithYes(): void {
		$result = $this->executeCodeSnippet("'hello'->endsWith('llo');");
		$this->assertEquals('true', $result);
	}

	public function testEndsWithNo(): void {
		$result = $this->executeCodeSnippet("'hello'->endsWith('he');");
		$this->assertEquals('false', $result);
	}

	public function testEndsWithInvalidParameter(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "'hello'->endsWith(23);");
	}

}