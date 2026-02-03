<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Real;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class BinaryLessThanEqualTest extends CodeExecutionTestHelper {

	public function testBinaryLessThanEqualTrue(): void {
		$result = $this->executeCodeSnippet("3.5 <= 5.14;");
		$this->assertEquals("true", $result);
	}

	public function testBinaryLessThanEqualSame(): void {
		$result = $this->executeCodeSnippet("3.5 <= 3.5;");
		$this->assertEquals("true", $result);
	}

	public function testBinaryLessThanEqualFalse(): void {
		$result = $this->executeCodeSnippet("5.14 <= 3.5;");
		$this->assertEquals("false", $result);
	}

	public function testBinaryLessThanEqualInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "3.5 <= false;");
	}
}