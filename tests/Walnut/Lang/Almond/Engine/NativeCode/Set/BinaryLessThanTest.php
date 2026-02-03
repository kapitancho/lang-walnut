<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Set;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class BinaryLessThanTest extends CodeExecutionTestHelper {

	public function testBinaryLessThanEmpty(): void {
		$result = $this->executeCodeSnippet("[;] < [;];");
		$this->assertEquals("false", $result);
	}

	public function testBinaryLessThanNonEmptyFalse(): void {
		$result = $this->executeCodeSnippet("[1; 2; 5; 10; 5] < [1; 2; 3; 4; 5];");
		$this->assertEquals("false", $result);
	}

	public function testBinaryLessThanNonEmptyTrue(): void {
		$result = $this->executeCodeSnippet("[1; 2; 5; 10; 5] < [1; 2; 3; 4; 5; 10];");
		$this->assertEquals("true", $result);
	}

	public function testBinaryLessThanNonEmptyEqual(): void {
		$result = $this->executeCodeSnippet("[1; 2; 5; 10; 5] < [1; 2; 2; 2; 5; 10];");
		$this->assertEquals("false", $result);
	}

	public function testBinaryLessThanInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "[1; 'a'] <= 5;");
	}

}