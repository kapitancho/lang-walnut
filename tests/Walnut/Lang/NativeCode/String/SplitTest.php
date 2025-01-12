<?php

namespace Walnut\Lang\NativeCode\String;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class SplitTest extends CodeExecutionTestHelper {

	public function testSplitOk(): void {
		$result = $this->executeCodeSnippet("'hello world !'->split(' ');");
		$this->assertEquals("['hello', 'world', '!']", $result);
	}

	public function testSplitNotNeeded(): void {
		$result = $this->executeCodeSnippet("'hello'->split(' ');");
		$this->assertEquals("['hello']", $result);
	}

	public function testSplitInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "'hello'->split(false);");
	}

}