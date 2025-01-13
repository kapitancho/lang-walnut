<?php

namespace Walnut\Lang\Test\Implementation\Code\Expression;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class RecordExpressionTest extends CodeExecutionTestHelper {

	public function testRecord(): void {
		$result = $this->executeCodeSnippet("[k: #];");
		$this->assertEquals("[k: []]", $result);
	}

}