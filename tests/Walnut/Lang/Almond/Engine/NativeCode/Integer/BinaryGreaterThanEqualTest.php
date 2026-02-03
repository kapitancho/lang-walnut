<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Integer;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class BinaryGreaterThanEqualTest extends CodeExecutionTestHelper {

	public function testBinaryGreaterThanEqualFalse(): void {
		$result = $this->executeCodeSnippet("3 >= 5;");
		$this->assertEquals("false", $result);
	}

	public function testBinaryGreaterThanEqualSame(): void {
		$result = $this->executeCodeSnippet("3 >= 3;");
		$this->assertEquals("true", $result);
	}

	public function testBinaryGreaterThanEqualTrue(): void {
		$result = $this->executeCodeSnippet("5 >= 3;");
		$this->assertEquals("true", $result);
	}

	public function testBinaryGreaterThanEqualInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "3 >= false;");
	}
}