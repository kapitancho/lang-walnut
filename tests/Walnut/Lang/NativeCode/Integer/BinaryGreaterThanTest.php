<?php

namespace Walnut\Lang\NativeCode\Integer;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class BinaryGreaterThanTest extends CodeExecutionTestHelper {

	public function testBinaryGreaterThanFalse(): void {
		$result = $this->executeCodeSnippet("3 > 5;");
		$this->assertEquals("false", $result);
	}

	public function testBinaryGreaterThanSame(): void {
		$result = $this->executeCodeSnippet("3 > 3;");
		$this->assertEquals("false", $result);
	}

	public function testBinaryGreaterThanTrue(): void {
		$result = $this->executeCodeSnippet("5 > 3;");
		$this->assertEquals("true", $result);
	}

	public function testBinaryGreaterThanInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "3.5 > false;");
	}
}