<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Array;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class ItemTest extends CodeExecutionTestHelper {

	public function testItemEmpty(): void {
		$this->executeErrorCodeSnippet(
			"Item access on [] with key Integer[4] is guaranteed to be empty",
			"[]->item(4);"
		);
	}

	public function testItemNonEmpty(): void {
		$result = $this->executeCodeSnippet("[1, 2]->item(1);");
		$this->assertEquals("2", $result);
	}

	public function testItemNonEmptyIndexOutOfRange(): void {
		$this->executeErrorCodeSnippet(
			"with key Integer[10] is guaranteed to be empty",
			"[1, 2, 5, 10, 5]->item(10);"
		);
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
			valueDeclarations: "arr = [1, 3, 5]; getValue = ^t: Integer => Optional<Integer> :: arr->item(t);"
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