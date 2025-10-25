<?php

namespace Walnut\Lang\Test\NativeCode\Array;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class FlipTest extends CodeExecutionTestHelper {

	public function testFlipEmpty(): void {
		$result = $this->executeCodeSnippet("[]->flip;");
		$this->assertEquals("[:]", $result);
	}

	public function testFlipNonEmpty(): void {
		$result = $this->executeCodeSnippet("['1', 'a']->flip;");
		$this->assertEquals("[1: 0, a: 1]", $result);
	}

	public function testFlipInvalidTargetType(): void {
		$this->executeErrorCodeSnippet('Invalid target type', "[1, 'a']->flip;");
	}
}