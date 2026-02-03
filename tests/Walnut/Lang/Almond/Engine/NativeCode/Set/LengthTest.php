<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Set;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class LengthTest extends CodeExecutionTestHelper {

	public function testLengthEmpty(): void {
		$result = $this->executeCodeSnippet("[;]->length;");
		$this->assertEquals("0", $result);
	}

	public function testLengthNonEmpty(): void {
		$result = $this->executeCodeSnippet("[1; 2; 2]->length;");
		$this->assertEquals("2", $result);
	}
}