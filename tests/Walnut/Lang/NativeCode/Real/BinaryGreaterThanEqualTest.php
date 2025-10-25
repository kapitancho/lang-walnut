<?php

namespace Walnut\Lang\Test\NativeCode\Real;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class BinaryGreaterThanEqualTest extends CodeExecutionTestHelper {

	public function testBinaryGreaterThanEqualFalse(): void {
		$result = $this->executeCodeSnippet("3.5 >= 5.14;");
		$this->assertEquals("false", $result);
	}

	public function testBinaryGreaterThanEqualSame(): void {
		$result = $this->executeCodeSnippet("3.5 >= 3.5;");
		$this->assertEquals("true", $result);
	}

	public function testBinaryGreaterThanEqualTrue(): void {
		$result = $this->executeCodeSnippet("5.14 >= 3.5;");
		$this->assertEquals("true", $result);
	}

	public function testBinaryGreaterThanEqualInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "3.5 >= false;");
	}
}