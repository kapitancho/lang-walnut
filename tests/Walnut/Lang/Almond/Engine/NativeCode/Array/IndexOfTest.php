<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Array;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class IndexOfTest extends CodeExecutionTestHelper {

	public function testIndexOfEmpty(): void {
		$result = $this->executeCodeSnippet("[]->indexOf(5);");
		$this->assertEquals("@ItemNotFound", $result);
	}

	public function testIndexOfNonEmpty(): void {
		$result = $this->executeCodeSnippet("[1, 2, 5, 10, 5]->indexOf(5);");
		$this->assertEquals("2", $result);
	}

	public function testIndexOfType(): void {
		$result = $this->executeCodeSnippet("i[1, 2, 5, 10, 5];",
			valueDeclarations: "i = ^a: Array<Integer> => Result<Integer, ItemNotFound> :: a->indexOf(5);"
		);
		$this->assertEquals("2", $result);
	}

	public function testIndexOfTypeRanged(): void {
		$result = $this->executeCodeSnippet("i[1, 2, 5, 10, 5];",
			valueDeclarations: "i = ^a: Array<Integer, 2..6> => Result<Integer<0..5>, ItemNotFound> :: a->indexOf(5);"
		);
		$this->assertEquals("2", $result);
	}
}