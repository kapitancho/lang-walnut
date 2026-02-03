<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Array;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class ItemTest extends CodeExecutionTestHelper {

	public function testItemEmpty(): void {
		$result = $this->executeCodeSnippet("[]->item(4);");
		$this->assertEquals("@IndexOutOfRange![index: 4]", $result);
	}

	public function testItemNonEmpty(): void {
		$result = $this->executeCodeSnippet("[1, 2]->item(1);");
		$this->assertEquals("2", $result);
	}

	public function testItemNonEmptyIndexOutOfRange(): void {
		$result = $this->executeCodeSnippet("[1, 2, 5, 10, 5]->item(10);");
		$this->assertEquals("@IndexOutOfRange![index: 10]", $result);
	}

	public function testItemInvalidParameterValue(): void {
		$this->executeErrorCodeSnippet("Invalid parameter type",
			"['a', 1, 2]->item('b')");
	}

	public function testItemTypeWithRestSubset(): void {
		$result = $this->executeCodeSnippet("getValue[6, true, false];",
			"MyTuple = [Integer, ... Boolean];",
			"getValue = ^t: MyTuple => Integer :: t.0;"
		);
		$this->assertEquals("6", $result);
	}

	public function testItemIntegerParameter(): void {
		$result = $this->executeCodeSnippet(
			"getValue(2);",
			valueDeclarations: "arr = [1, 3, 5]; getValue = ^t: Integer => Result<Integer, IndexOutOfRange> :: arr->item(t);"
		);
		$this->assertEquals("5", $result);
	}

	public function testItemIntegerParameterWithinRange(): void {
		$result = $this->executeCodeSnippet(
			"getValue(2);",
			valueDeclarations: "arr = [1, 3, 5]; getValue = ^t: Integer<1..2> => Integer :: arr->item(t);"
		);
		$this->assertEquals("5", $result);
	}

}