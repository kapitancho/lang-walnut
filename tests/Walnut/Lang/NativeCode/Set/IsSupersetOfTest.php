<?php

namespace Walnut\Lang\NativeCode\Set;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class IsSupersetOfTest extends CodeExecutionTestHelper {

	public function testIsSupersetOfEmpty(): void {
		$result = $this->executeCodeSnippet("[;]->isSupersetOf([;]);");
		$this->assertEquals("true", $result);
	}

	public function testIsSupersetOfNonEmptyFalse(): void {
		$result = $this->executeCodeSnippet("[1; 2; 5; 10; 5]->isSupersetOf[1; 2; 3; 4; 5];");
		$this->assertEquals("false", $result);
	}

	public function testIsSupersetOfNonEmptyTrue(): void {
		$result = $this->executeCodeSnippet("[1; 2; 5; 10; 5]->isSupersetOf[1; 2];");
		$this->assertEquals("true", $result);
	}

	public function testIsSupersetOfInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "[1; 'a']->isSupersetOf(5);");
	}

}