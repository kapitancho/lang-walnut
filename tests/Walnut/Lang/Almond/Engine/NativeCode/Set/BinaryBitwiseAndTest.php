<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Set;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class BinaryBitwiseAndTest extends CodeExecutionTestHelper {

	public function testBinaryBitwiseAndEmpty(): void {
		$result = $this->executeCodeSnippet("[;] & [;];");
		$this->assertEquals("[;]", $result);
	}

	public function testBinaryBitwiseAndNonEmptyWithoutCommon(): void {
		$result = $this->executeCodeSnippet("[1; 2] & [3; 4];");
		$this->assertEquals("[;]", $result);
	}

	public function testBinaryBitwiseAndNonEmptyWithCommon(): void {
		$result = $this->executeCodeSnippet("[1; 2] & [2; 3];");
		$this->assertEquals("[2;]", $result);
	}

	public function testBinaryBitwiseAndInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "[1; 2] & 5;");
	}

}