<?php

namespace Walnut\Lang\NativeCode\Record;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class ItemValuesTest extends CodeExecutionTestHelper {

	public function testItemValues(): void {
		$result = $this->executeCodeSnippet("getItemValues[a: 3, b: 'hello'];",
			valueDeclarations: "getItemValues = ^Record => Map :: #->itemValues;");
		$this->assertEquals("[a: 3, b: 'hello']", $result);
	}

}