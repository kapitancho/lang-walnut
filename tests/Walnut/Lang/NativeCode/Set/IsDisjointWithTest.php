<?php

namespace Walnut\Lang\NativeCode\Set;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class IsDisjointWithTest extends CodeExecutionTestHelper {

	public function testIsDisjointWithEmpty(): void {
		$result = $this->executeCodeSnippet("[;]->isDisjointWith([;]);");
		$this->assertEquals("true", $result);
	}

	public function testIsDisjointWithNonEmptyWithoutCommon(): void {
		$result = $this->executeCodeSnippet("[1; 2]->isDisjointWith[3; 4];");
		$this->assertEquals("true", $result);
	}

	public function testIsDisjointWithNonEmptyWithCommon(): void {
		$result = $this->executeCodeSnippet("[1; 2]->isDisjointWith[2; 3];");
		$this->assertEquals("false", $result);
	}

	public function testIsDisjointWithInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "[1; 2]->isDisjointWith(5);");
	}

}