<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Bytes;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class OrdTest extends CodeExecutionTestHelper {

	public function testOrd(): void {
		$result = $this->executeCodeSnippet('"a"->ord;');
		$this->assertEquals('97', $result);
	}

	public function testOrdInvalidTargetEmpty(): void {
		$this->executeErrorCodeSnippet('Target type Bytes<0> is not a subtype of Bytes<1>', '""->ord;');
	}

	public function testOrdInvalidTargetLong(): void {
		$this->executeErrorCodeSnippet('Target type Bytes<3> is not a subtype of Bytes<1>', '"abc"->ord;');
	}

	public function testOrdInvalidParameter(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', '"a"->ord(5);');
	}

}
