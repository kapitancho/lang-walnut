<?php

namespace Walnut\Lang\NativeCode\Map;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class FlipTest extends CodeExecutionTestHelper {

	public function testFlipEmpty(): void {
		$result = $this->executeCodeSnippet("[:]->flip;");
		$this->assertEquals("[:]", $result);
	}

	public function testFlipNonEmpty(): void {
		$result = $this->executeCodeSnippet("[a: '1', b: 'a']->flip;");
		$this->assertEquals("[1: 'a', a: 'b']", $result);
	}

	public function testFlipInvalidTargetType(): void {
		$this->executeErrorCodeSnippet('Invalid target type', "[a: 1, b: 'a']->flip;");
	}
}