<?php

namespace Walnut\Lang\Test\Almond\Engine\Implementation\Expression;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class ReturnExpressionTest extends CodeExecutionTestHelper {

	public function testReturn(): void {
		$result = $this->executeCodeSnippet("=> 5; 10;");
		$this->assertEquals("5", $result);
	}

}