<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Integer;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class ChrTest extends CodeExecutionTestHelper {

	public function testChr(): void {
		$result = $this->executeCodeSnippet("65->chr;");
		$this->assertEquals('"A"', $result);
	}

	public function testChrInvalidTargetNegative(): void {
		$this->executeErrorCodeSnippet('Invalid target type', '-10->chr;');
	}

	public function testChrInvalidTargetLarge(): void {
		$this->executeErrorCodeSnippet('Invalid target type', '23410->chr;');
	}

	public function testChrInvalidParameter(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', '99->chr(5);');
	}

}