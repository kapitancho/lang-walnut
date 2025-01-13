<?php

namespace Walnut\Lang\Test\Implementation\Code\Expression;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class TupleExpressionTest extends CodeExecutionTestHelper {

	public function testTuple(): void {
		$result = $this->executeCodeSnippet("[#];");
		$this->assertEquals("[[]]", $result);
	}

}