<?php

namespace Walnut\Lang\Test\NativeCode\Map;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class MergeWithTest extends CodeExecutionTestHelper {

	public function testMergeWithEmpty(): void {
		$result = $this->executeCodeSnippet("[:]->mergeWith([:]);");
		$this->assertEquals("[:]", $result);
	}

	public function testMergeWithNonEmpty(): void {
		$result = $this->executeCodeSnippet("[a: 1, b: 2]->mergeWith[b: 3, c: 4];");
		$this->assertEquals("[a: 1, b: 3, c: 4]", $result);
	}

	public function testMergeWithInvalidType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "[a: 1, b: 2]->mergeWith(3);");
	}
}