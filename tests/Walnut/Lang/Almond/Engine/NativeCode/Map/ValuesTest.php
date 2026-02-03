<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Map;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class ValuesTest extends CodeExecutionTestHelper {

	public function testValuesEmpty(): void {
		$result = $this->executeCodeSnippet("[:]->values;");
		$this->assertEquals("[]", $result);
	}

	public function testValuesNonEmpty(): void {
		$result = $this->executeCodeSnippet("[a: 1, b: 2, c: 5.3, d: 2]->values;");
		$this->assertEquals("[1, 2, 5.3, 2]", $result);
	}

}