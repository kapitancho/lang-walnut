<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Bytes;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class SplitTest extends CodeExecutionTestHelper {

	public function testSplitOk(): void {
		$result = $this->executeCodeSnippet('"hello world !"->split(" ");');
		$this->assertEquals('["hello", "world", "!"]', $result);
	}

	public function testSplitNotNeeded(): void {
		$result = $this->executeCodeSnippet('"hello"->split(" ");');
		$this->assertEquals('["hello"]', $result);
	}

	public function testSplitEmptyString(): void {
		$this->executeErrorCodeSnippet(
			"Parameter type Bytes<0> is not a subtype of Bytes<1..>",
			'"hello"->split("");');
	}

	public function testSplitInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', '"hello"->split(false);');
	}

}
