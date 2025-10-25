<?php

namespace Walnut\Lang\Test\NativeCode\Set;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class IsSubsetOfTest extends CodeExecutionTestHelper {

	public function testIsSubsetOfEmpty(): void {
		$result = $this->executeCodeSnippet("[;]->isSubsetOf([;]);");
		$this->assertEquals("true", $result);
	}

	public function testIsSubsetOfNonEmptyFalse(): void {
		$result = $this->executeCodeSnippet("[1; 2; 5; 10; 5]->isSubsetOf[1; 2; 3; 4; 5];");
		$this->assertEquals("false", $result);
	}

	public function testIsSubsetOfNonEmptyTrue(): void {
		$result = $this->executeCodeSnippet("[1; 2; 5; 10; 5]->isSubsetOf[1; 2; 3; 4; 5; 10];");
		$this->assertEquals("true", $result);
	}

	public function testIsSubsetOfInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "[1; 'a']->isSubsetOf(5);");
	}

}