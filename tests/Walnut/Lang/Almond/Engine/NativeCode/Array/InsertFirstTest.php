<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Array;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class InsertFirstTest extends CodeExecutionTestHelper {

	public function testInsertFirstEmpty(): void {
		$result = $this->executeCodeSnippet("[]->insertFirst(1);");
		$this->assertEquals("[1]", $result);
	}

	public function testInsertFirstNonEmpty(): void {
		$result = $this->executeCodeSnippet("[1, 2]->insertFirst('a');");
		$this->assertEquals("['a', 1, 2]", $result);
	}

	public function testInsertFirstType(): void {
		$result = $this->executeCodeSnippet("i[1, 2];",
			valueDeclarations: "i = ^a: Array<Integer, 1..3> => Array<String|Integer, 2..4> :: a->insertFirst('a');"
		);
		$this->assertEquals("['a', 1, 2]", $result);
	}
}