<?php

namespace Walnut\Lang\Test\Almond\Unsorted;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class MultiVariableAssignmentTest extends CodeExecutionTestHelper {

	public function testAssignmentOk(): void {
		$result = $this->executeCodeSnippet(
			"var{a: x} = A; x;",
			<<<NUT
				A := ();
				A->item(^x: String) :: 'item-' + x;
			NUT
		);
		$this->assertEquals("'item-a'", $result);
	}

	public function testAssignmentNoItemMethod(): void {
		$this->executeErrorCodeSnippet(
			"Method 'item' is not defined for type 'A'.",
			"var{a: x} = A; x;",
			<<<NUT
				A := ();
			NUT
		);
	}

}