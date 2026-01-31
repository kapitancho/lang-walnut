<?php

namespace Walnut\Lang\Test\Almond\Engine\Implementation\Expression;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class RecordExpressionTest extends CodeExecutionTestHelper {

	public function testRecord(): void {
		$result = $this->executeCodeSnippet("[k: #];");
		$this->assertEquals("[k: []]", $result);
	}

}