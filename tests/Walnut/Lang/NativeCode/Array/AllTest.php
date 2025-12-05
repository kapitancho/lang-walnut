<?php

namespace Walnut\Lang\Test\NativeCode\Array;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class AllTest extends CodeExecutionTestHelper {

	public function testAllEmpty(): void {
		$result = $this->executeCodeSnippet("[]->all(^String => True :: true);");
		$this->assertEquals("true", $result);
	}

	public function testAllEmptyAlwaysFalse(): void {
		$result = $this->executeCodeSnippet("[]->all(^Any => False :: false);");
		$this->assertEquals("true", $result);
	}

	public function testAllNonEmptyNoneMatch(): void {
		$result = $this->executeCodeSnippet("[1, 2, 3]->all(^i: Integer => Boolean :: i > 10);");
		$this->assertEquals("false", $result);
	}

	public function testAllNonEmptySomeMatch(): void {
		$result = $this->executeCodeSnippet("[1, 2, 5, 10, 3]->all(^i: Integer => Boolean :: i > 4);");
		$this->assertEquals("false", $result);
	}

	public function testAllNonEmptyAllMatch(): void {
		$result = $this->executeCodeSnippet("[5, 10, 15]->all(^i: Integer<0..20> => Boolean :: i > 4);");
		$this->assertEquals("true", $result);
	}

	public function testAllFirstElementFails(): void {
		$result = $this->executeCodeSnippet("[1, 10, 20]->all(^i: Integer => Boolean :: i > 4);");
		$this->assertEquals("false", $result);
	}

	public function testAllLastElementFails(): void {
		$result = $this->executeCodeSnippet("[10, 20, 1]->all(^i: Integer => Boolean :: i > 4);");
		$this->assertEquals("false", $result);
	}

	public function testAllWithStrings(): void {
		$result = $this->executeCodeSnippet("['hello', 'hi', 'hey']->all(^s: String => Boolean :: s->startsWith('h'));");
		$this->assertEquals("true", $result);
	}

	public function testAllInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "[1, 2]->all(5);");
	}

	public function testAllInvalidParameterParameterType(): void {
		$this->executeErrorCodeSnippet("The parameter type (Integer[1]|String['a']) of the callback function is not a subtype of Boolean",
			"[1, 'a']->all(^Boolean => Boolean :: true);");
	}

	public function testAllInvalidParameterReturnType(): void {
		$this->executeErrorCodeSnippet("Invalid parameter type",
			"[1, 2]->all(^Any => Real :: 3.14);");
	}

}
