<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\String;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class SplitTest extends CodeExecutionTestHelper {

	public function testSplitOk(): void {
		$result = $this->executeCodeSnippet("'hello world !'->split(' ');");
		$this->assertEquals("['hello', 'world', '!']", $result);
	}

	public function testSplitNotNeeded(): void {
		$result = $this->executeCodeSnippet("'hello'->split(' ');");
		$this->assertEquals("['hello']", $result);
	}

	public function testSplitEmptyString(): void {
		$this->executeErrorCodeSnippet(
			"Expected a subtype of String<1..>, but got String['']",
			"'hello'->split('');");
	}

	public function testSplitInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "'hello'->split(false);");
	}

}