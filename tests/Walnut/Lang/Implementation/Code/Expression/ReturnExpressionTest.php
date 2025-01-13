<?php

namespace Walnut\Lang\Test\Implementation\Code\Expression;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class ReturnExpressionTest extends CodeExecutionTestHelper {

	public function testReturn(): void {
		$result = $this->executeCodeSnippet("=> 5; 10;");
		$this->assertEquals("5", $result);
	}

}