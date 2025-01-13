<?php

namespace Walnut\Lang\Test\Implementation\Code\Expression;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class SetExpressionTest extends CodeExecutionTestHelper {

	public function testSet(): void {
		$result = $this->executeCodeSnippet("[#;];");
		$this->assertEquals("[[];]", $result);
	}

}