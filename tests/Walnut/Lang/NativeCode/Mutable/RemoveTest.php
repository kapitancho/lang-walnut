<?php

namespace Walnut\Lang\NativeCode\Mutable;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class RemoveTest extends CodeExecutionTestHelper {

	public function testRemoveNew(): void {
		$result = $this->executeCodeSnippet("mutable{Set, [1; 2; 3]}->REMOVE(5);");
		$this->assertEquals("@ItemNotFound()", $result);
	}

	public function testRemoveExisting(): void {
		$result = $this->executeCodeSnippet("mutable{Set, [1; 2; 3]}->REMOVE(2);");
		$this->assertEquals("2", $result);
	}

	public function testRemoveInvalidTargetType(): void {
		$this->executeErrorCodeSnippet('Invalid target type', "mutable{Real, 3.14}->REMOVE(2);");
	}

}