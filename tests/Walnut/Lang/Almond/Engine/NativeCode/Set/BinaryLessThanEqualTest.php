<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Set;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class BinaryLessThanEqualTest extends CodeExecutionTestHelper {

	public function testBinaryLessThanEqualEmpty(): void {
		$result = $this->executeCodeSnippet("[;] <= [;];");
		$this->assertEquals("true", $result);
	}

	public function testBinaryLessThanEqualNonEmptyFalse(): void {
		$result = $this->executeCodeSnippet("[1; 2; 5; 10; 5] <= [1; 2; 3; 4; 5];");
		$this->assertEquals("false", $result);
	}

	public function testBinaryLessThanEqualNonEmptyTrue(): void {
		$result = $this->executeCodeSnippet("[1; 2; 5; 10; 5] <= [1; 2; 3; 4; 5; 10];");
		$this->assertEquals("true", $result);
	}

	public function testBinaryLessThanEqualNonEmptyEqual(): void {
		$result = $this->executeCodeSnippet("[1; 2; 5; 10; 5] <= [1; 2; 2; 2; 5; 10];");
		$this->assertEquals("true", $result);
	}

	public function testBinaryLessThanEqualInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "[1; 'a'] <= 5;");
	}

}