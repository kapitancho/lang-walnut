<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Map;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class AsArrayTest extends CodeExecutionTestHelper {

	public function testAsArrayEmpty(): void {
		$result = $this->executeCodeSnippet("[:]->as(`Array);");
		$this->assertEquals("[]", $result);
	}

	public function testAsArrayNonEmpty(): void {
		$result = $this->executeCodeSnippet("[a: 1, b: 2, c: 5.3, d: 2]->as(`Array);");
		$this->assertEquals("[1, 2, 5.3, 2]", $result);
	}

}