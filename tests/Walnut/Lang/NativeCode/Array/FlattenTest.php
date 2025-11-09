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

	public function testFlattenReturnType(): void {
		$result = $this->executeCodeSnippet(
			"flatten[[1, 3, 7, 10], [0, 2, 5], [-4, 29]];",
			valueDeclarations: "flatten = ^arr: Array<Array<Integer, 2..5>, 3..5> => Array<Integer, 6..25> :: arr->flatten;"
		);
		$this->assertEquals("[1, 3, 7, 10, 0, 2, 5, -4, 29]", $result);
	}

	public function testFlattenReturnTypeNoMaxLength(): void {
		$result = $this->executeCodeSnippet(
			"flatten[[1, 3, 7, 10], [0, 2, 5], [-4, 29]];",
			valueDeclarations: "flatten = ^arr: Array<Array<Integer, 2..5>, 3..> => Array<Integer, 6..> :: arr->flatten;"
		);
		$this->assertEquals("[1, 3, 7, 10, 0, 2, 5, -4, 29]", $result);
	}

	public function testFlattenInvalidTargetType(): void {
		$this->executeErrorCodeSnippet('Invalid target type', "[1, [0, 2, 5], [], [[[22]], 9]]->flatten;");
	}
}