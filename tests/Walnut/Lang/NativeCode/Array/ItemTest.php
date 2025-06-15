<?php

namespace Walnut\Lang\NativeCode\Array;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class ItemTest extends CodeExecutionTestHelper {

	public function testItemEmpty(): void {
		$result = $this->executeCodeSnippet("[]->item(4);");
		$this->assertEquals("@IndexOutOfRange!!!!![index: 4]", $result);
	}

	public function testItemNonEmpty(): void {
		$result = $this->executeCodeSnippet("[1, 2]->item(1);");
		$this->assertEquals("2", $result);
	}

	public function testItemNonEmptyIndexOutOfRange(): void {
		$result = $this->executeCodeSnippet("[1, 2, 5, 10, 5]->item(10);");
		$this->assertEquals("@IndexOutOfRange!!!!![index: 10]", $result);
	}

	public function testItemInvalidParameterValue(): void {
		$this->executeErrorCodeSnippet("Invalid parameter type",
			"['a', 1, 2]->item('b')");
	}
}