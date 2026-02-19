<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Array;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class LastIndexOfTest extends CodeExecutionTestHelper {

	public function testLastIndexOfEmpty(): void {
		$result = $this->executeCodeSnippet("[]->lastIndexOf(5);");
		$this->assertEquals("@ItemNotFound", $result);
	}

	public function testLastIndexOfNonEmpty(): void {
		$result = $this->executeCodeSnippet("[1, 2, 5, 10, 5]->lastIndexOf(5);");
		$this->assertEquals("4", $result);
	}

	public function testIndexOfType(): void {
		$result = $this->executeCodeSnippet("i[1, 2, 5, 10, 5];",
			valueDeclarations: "i = ^a: Array<Integer> => Result<Integer, ItemNotFound> :: a->lastIndexOf(5);"
		);
		$this->assertEquals("4", $result);
	}

	public function testIndexOfTypeRanged(): void {
		$result = $this->executeCodeSnippet("i[1, 2, 5, 10, 5];",
			valueDeclarations: "i = ^a: Array<Integer, 2..6> => Result<Integer<0..5>, ItemNotFound> :: a->lastIndexOf(5);"
		);
		$this->assertEquals("4", $result);
	}
}