<?php

namespace Walnut\Lang\NativeCode\Integer;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class AsIntegerTest extends CodeExecutionTestHelper {

	public function testAsInteger(): void {
		$result = $this->executeCodeSnippet("3->asInteger;");
		$this->assertEquals("3", $result);
	}
}