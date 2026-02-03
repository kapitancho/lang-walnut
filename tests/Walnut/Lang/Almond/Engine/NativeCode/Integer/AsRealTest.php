<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Integer;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class AsRealTest extends CodeExecutionTestHelper {

	public function testAsReal(): void {
		$result = $this->executeCodeSnippet("3->asReal;");
		$this->assertEquals("3", $result);
	}

	public function testAsRealRange(): void {
		$result = $this->executeCodeSnippet("#->length->asReal;");
		$this->assertEquals("0", $result);
	}
}