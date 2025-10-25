<?php

namespace Walnut\Lang\Test\Feature\Type;

use Walnut\Lang\Test\CodeExecutionTestHelper;

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
			"Cannot call method 'item' on type 'A'",
			"var{a: x} = A; x;",
			<<<NUT
				A := ();
			NUT
		);
	}

}