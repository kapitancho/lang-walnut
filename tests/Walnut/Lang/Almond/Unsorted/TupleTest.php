<?php

namespace Walnut\Lang\Test\Almond\Unsorted;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class TupleTest extends CodeExecutionTestHelper {

	public function testCustomMethodOnTuple(): void {
		$result = $this->executeCodeSnippet(
			"[3.14, 1]->m;",
			"
				G = [Real, Real];
				G->m(=> Real) :: $0 + $1;
			"
		);
		$this->assertEquals("4.14", $result);
	}

}