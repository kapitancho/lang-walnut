<?php

namespace Walnut\Lang\NativeCode\Real;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class BinaryLessThanTest extends CodeExecutionTestHelper {

	public function testBinaryLessThanTrue(): void {
		$result = $this->executeCodeSnippet("3.5 < 5.14;");
		$this->assertEquals("true", $result);
	}

	public function testBinaryLessThanSame(): void {
		$result = $this->executeCodeSnippet("3.5 < 3.5;");
		$this->assertEquals("false", $result);
	}

	public function testBinaryLessThanFalse(): void {
		$result = $this->executeCodeSnippet("5.14 < 3.5;");
		$this->assertEquals("false", $result);
	}

	public function testBinaryLessThanInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "3.5 < false;");
	}
}