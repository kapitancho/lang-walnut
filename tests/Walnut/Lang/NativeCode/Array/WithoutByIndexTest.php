<?php

namespace Walnut\Lang\Test\NativeCode\Array;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class WithoutByIndexTest extends CodeExecutionTestHelper {

	public function testWithoutByIndexEmpty(): void {
		$result = $this->executeCodeSnippet("[]->withoutByIndex(3);");
		$this->assertEquals("@IndexOutOfRange![index: 3]", $result);
	}

	public function testWithoutByIndexNotFound(): void {
		$result = $this->executeCodeSnippet("[1, 2, 5, 10, 5]->withoutByIndex(13);");
		$this->assertEquals("@IndexOutOfRange![index: 13]", $result);
	}

	public function testWithoutByIndexNonEmpty(): void {
		$result = $this->executeCodeSnippet("[1, 2, 5, 10, 5]->withoutByIndex(2);");
		$this->assertEquals("[element: 5, array: [1, 2, 10, 5]]", $result);
	}

	public function testWithoutByIndexInvalidParameterValue(): void {
		$this->executeErrorCodeSnippet("Invalid parameter type",
			"['a', 1, 2]->withoutByIndex('b')");
	}
}