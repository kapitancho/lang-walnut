<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Array;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class InsertAtTest extends CodeExecutionTestHelper {

	public function testInsertAtEmpty(): void {
		$result = $this->executeCodeSnippet("[]->insertAt[value: 1, index: 0];");
		$this->assertEquals("[1]", $result);
	}

	public function testInsertAtNonEmpty(): void {
		$result = $this->executeCodeSnippet("['a', 1, 2]->insertAt[value: 'b', index: 2];");
		$this->assertEquals("['a', 1, 'b', 2]", $result);
	}

	public function testInsertAtIndexOutOfRange(): void {
		$result = $this->executeCodeSnippet("['a', 1, 2]->insertAt[value: 'b', index: 12];");
		$this->assertEquals("@IndexOutOfRange![index: 12]", $result);
	}

	public function testInsertAtInvalidParameterValue(): void {
		$this->executeErrorCodeSnippet("Invalid parameter type",
			"['a', 1, 2]->insertAt[value: 'b']");
	}

	public function testInsertAtInvalidParameterType(): void {
		$this->executeErrorCodeSnippet("Invalid parameter type",
			"['a', 1, 2]->insertAt('b')");
	}
}