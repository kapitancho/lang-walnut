<?php

namespace Walnut\Lang\NativeCode\Tuple;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class ItemValuesTest extends CodeExecutionTestHelper {

	public function testItemValues(): void {
		$result = $this->executeCodeSnippet("getItemValues[3, 'hello'];",
			"getItemValues = ^Tuple => Array :: #->itemValues;");
		$this->assertEquals("[3, 'hello']", $result);
	}

}