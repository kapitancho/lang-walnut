<?php

namespace Walnut\Lang\NativeCode\Array;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class FindLastTest extends CodeExecutionTestHelper {

	public function testFindLastEmpty(): void {
		$result = $this->executeCodeSnippet("[]->findLast(^Any => Boolean :: true);");
		$this->assertEquals("@ItemNotFound", $result);
	}

	public function testFindLastNonEmpty(): void {
		$result = $this->executeCodeSnippet("[1, 2, 5, 10, 5]->findLast(^Integer => Boolean :: # > 4);");
		$this->assertEquals("5", $result);
	}

	public function testFindLastInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "[1, 'a']->findLast(5);");
	}

	public function testFindLastInvalidParameterParameterType(): void {
		$this->executeErrorCodeSnippet("The parameter type (Integer[1]|String['a']) of the callback function is not a subtype of Boolean",
			"[1, 'a']->findLast(^Boolean => Boolean :: true);");
	}

	public function testFindLastInvalidParameterReturnType(): void {
		$this->executeErrorCodeSnippet("Invalid parameter type",
			"[1, 'a']->findLast(^Any => Real :: 3.14);");
	}

}