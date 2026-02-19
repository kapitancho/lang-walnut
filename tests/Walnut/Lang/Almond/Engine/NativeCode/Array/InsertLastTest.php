<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Array;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class InsertLastTest extends CodeExecutionTestHelper {

	public function testInsertLastEmpty(): void {
		$result = $this->executeCodeSnippet("[]->insertLast(1);");
		$this->assertEquals("[1]", $result);
	}

	public function testInsertLastNonEmpty(): void {
		$result = $this->executeCodeSnippet("[1, 2]->insertLast('a');");
		$this->assertEquals("[1, 2, 'a']", $result);
	}

	public function testInsertLastType(): void {
		$result = $this->executeCodeSnippet("i[1, 2];",
			valueDeclarations: "i = ^a: Array<Integer, 1..3> => Array<String|Integer, 2..4> :: a->insertLast('a');"
		);
		$this->assertEquals("[1, 2, 'a']", $result);
	}
}