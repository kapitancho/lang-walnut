<?php

namespace Walnut\Lang\NativeCode\Array;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class FindFirstTest extends CodeExecutionTestHelper {

	public function testFindFirstEmpty(): void {
		$result = $this->executeCodeSnippet("[]->findFirst(^Any => Boolean :: true);");
		$this->assertEquals("@ItemNotFound", $result);
	}

	public function testFindFirstNonEmpty(): void {
		$result = $this->executeCodeSnippet("[1, 2, 5, 10, 5]->findFirst(^Integer => Boolean :: # > 4);");
		$this->assertEquals("5", $result);
	}

	public function testFindFirstInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "[1, 'a']->findFirst(5);");
	}

	public function testFindFirstInvalidParameterParameterType(): void {
		$this->executeErrorCodeSnippet("The parameter type (Integer[1]|String['a']) of the callback function is not a subtype of Boolean",
			"[1, 'a']->findFirst(^Boolean => Boolean :: true);");
	}

	public function testFindFirstInvalidParameterReturnType(): void {
		$this->executeErrorCodeSnippet("Invalid parameter type",
			"[1, 'a']->findFirst(^Any => Real :: 3.14);");
	}

}