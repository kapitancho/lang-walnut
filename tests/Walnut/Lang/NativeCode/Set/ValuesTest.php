<?php

namespace Walnut\Lang\NativeCode\Set;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class ValuesTest extends CodeExecutionTestHelper {

	public function testValuesEmpty(): void {
		$result = $this->executeCodeSnippet("[;]->values;");
		$this->assertEquals("[]", $result);
	}

	public function testValuesNonEmpty(): void {
		$result = $this->executeCodeSnippet("[1; 2; 5.3; 2]->values;");
		$this->assertEquals("[1, 2, 5.3]", $result);
	}

}