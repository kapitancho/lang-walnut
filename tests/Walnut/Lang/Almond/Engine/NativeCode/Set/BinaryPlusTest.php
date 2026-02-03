<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Set;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class BinaryPlusTest extends CodeExecutionTestHelper {

	public function testBinaryPlusEmpty(): void {
		$result = $this->executeCodeSnippet("[;] + [;];");
		$this->assertEquals("[;]", $result);
	}

	public function testBinaryPlusNonEmptyWithoutCommon(): void {
		$result = $this->executeCodeSnippet("[1; 2] + [3; 4];");
		$this->assertEquals("[1; 2; 3; 4]", $result);
	}

	public function testBinaryPlusNonEmptyWithCommon(): void {
		$result = $this->executeCodeSnippet("[1; 2] + [2; 3];");
		$this->assertEquals("[1; 2; 3]", $result);
	}

	public function testBinaryPlusInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "[1; 2] + 5;");
	}

}