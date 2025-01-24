<?php

namespace Walnut\Lang\NativeCode\String;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class BinaryLessThanEqualTest extends CodeExecutionTestHelper {

	public function testBinaryLessThanEqualFalse(): void {
		$result = $this->executeCodeSnippet("'ac' <= 'abc';");
		$this->assertEquals("false", $result);
	}

	public function testBinaryLessThanEqualSame(): void {
		$result = $this->executeCodeSnippet("'abc' <= 'abc';");
		$this->assertEquals("true", $result);
	}

	public function testBinaryLessThanEqualTrue(): void {
		$result = $this->executeCodeSnippet("'abc' <= 'ac';");
		$this->assertEquals("true", $result);
	}

	public function testBinaryLessThanEqualInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "'a' <= false;");
	}
}