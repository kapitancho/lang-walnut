<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Set;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class BinaryMinusTest extends CodeExecutionTestHelper {

	public function testBinaryMinusEmpty(): void {
		$result = $this->executeCodeSnippet("[;] - [;];");
		$this->assertEquals("[;]", $result);
	}

	public function testBinaryMinusNonEmptyWithoutCommon(): void {
		$result = $this->executeCodeSnippet("[1; 2] - [3; 4];");
		$this->assertEquals("[1; 2]", $result);
	}

	public function testBinaryMinusNonEmptyWithCommon(): void {
		$result = $this->executeCodeSnippet("[1; 2] - [2; 3];");
		$this->assertEquals("[1;]", $result);
	}

	public function testBinaryMinusInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "[1; 2] - 5;");
	}

}