<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Set;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class BinaryBitwiseXorTest extends CodeExecutionTestHelper {

	public function testBinaryBitwiseXorEmpty(): void {
		$result = $this->executeCodeSnippet("[;] ^ [;];");
		$this->assertEquals("[;]", $result);
	}

	public function testBinaryBitwiseXorNonEmptyWithoutCommon(): void {
		$result = $this->executeCodeSnippet("[1; 2] ^ [3; 4];");
		$this->assertEquals("[1; 2; 3; 4]", $result);
	}

	public function testBinaryBitwiseXorNonEmptyWithCommon(): void {
		$result = $this->executeCodeSnippet("[1; 2] ^ [2; 3];");
		$this->assertEquals("[1; 3]", $result);
	}

	public function testBinaryBitwiseXorInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "[1; 2] ^ 5;");
	}

}