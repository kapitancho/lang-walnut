<?php

namespace Walnut\Lang\Test\NativeCode\Array;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class FlattenTest extends CodeExecutionTestHelper {

	public function testFlattenEmpty(): void {
		$result = $this->executeCodeSnippet("[]->flatten;");
		$this->assertEquals("[]", $result);
	}

	public function testFlattenNonEmpty(): void {
		$result = $this->executeCodeSnippet("[[1], [0, 2, 5], [], [[[22]], 9]]->flatten;");
		$this->assertEquals("[1, 0, 2, 5, [[22]], 9]", $result);
	}

	public function testFlattenInvalidTargetType(): void {
		$this->executeErrorCodeSnippet('Invalid target type', "[1, [0, 2, 5], [], [[[22]], 9]]->flatten;");
	}
}