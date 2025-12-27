<?php

namespace Walnut\Lang\NativeCode\Set;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class BinaryGreaterThanTest extends CodeExecutionTestHelper {

	public function testIsSupersetOfEmpty(): void {
		$result = $this->executeCodeSnippet("[;] > [;];");
		$this->assertEquals("false", $result);
	}

	public function testIsSupersetOfNonEmptyFalse(): void {
		$result = $this->executeCodeSnippet("[1; 2; 5; 10; 5] > [1; 2; 3; 4; 5];");
		$this->assertEquals("false", $result);
	}

	public function testIsSupersetOfNonEmptyTrue(): void {
		$result = $this->executeCodeSnippet("[1; 2; 5; 10; 5] > [1; 2];");
		$this->assertEquals("true", $result);
	}

	public function testIsSupersetOfNonEmptyEqual(): void {
		$result = $this->executeCodeSnippet("[1; 2; 5; 10; 5] > [1; 2; 2; 2; 5; 10];");
		$this->assertEquals("false", $result);
	}

	public function testIsSupersetOfInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "[1; 'a'] > 5;");
	}

}