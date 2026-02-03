<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Bytes;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class OrdTest extends CodeExecutionTestHelper {

	public function testOrd(): void {
		$result = $this->executeCodeSnippet('"a"->ord;');
		$this->assertEquals('97', $result);
	}

	public function testOrdInvalidTargetEmpty(): void {
		$this->executeErrorCodeSnippet('Invalid target type', '""->ord;');
	}

	public function testOrdInvalidTargetLong(): void {
		$this->executeErrorCodeSnippet('Invalid target type', '"abc"->ord;');
	}

	public function testOrdInvalidParameter(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', '"a"->ord(5);');
	}

}
