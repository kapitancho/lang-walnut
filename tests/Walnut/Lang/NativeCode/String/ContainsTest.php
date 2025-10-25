<?php

namespace Walnut\Lang\Test\NativeCode\String;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class ContainsTest extends CodeExecutionTestHelper {

	public function testContainsYes(): void {
		$result = $this->executeCodeSnippet("'hello'->contains('lo');");
		$this->assertEquals('true', $result);
	}

	public function testContainsNo(): void {
		$result = $this->executeCodeSnippet("'hello'->contains('elo');");
		$this->assertEquals('false', $result);
	}

	public function testContainsInvalidParameter(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "'hello'->contains(23);");
	}

}