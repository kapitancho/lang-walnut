<?php

namespace Walnut\Lang\Test\NativeCode\Array;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class AnyTest extends CodeExecutionTestHelper {

	public function testAnyEmpty(): void {
		$result = $this->executeCodeSnippet("[]->any(^Real => True :: true);");
		$this->assertEquals("false", $result);
	}

	public function testAnyEmptyAlwaysFalse(): void {
		$result = $this->executeCodeSnippet("[]->any(^Any => False :: false);");
		$this->assertEquals("false", $result);
	}

	public function testAnyNonEmptyNoneMatch(): void {
		$result = $this->executeCodeSnippet("[1, 2, 3]->any(^i: Integer => Boolean :: i > 10);");
		$this->assertEquals("false", $result);
	}

	public function testAnyNonEmptySomeMatch(): void {
		$result = $this->executeCodeSnippet("[1, 2, 5, 10, 3]->any(^i: Integer => Boolean :: i > 4);");
		$this->assertEquals("true", $result);
	}

	public function testAnyNonEmptyAllMatch(): void {
		$result = $this->executeCodeSnippet("[5, 10, 15]->any(^i: Integer<0..20> => Boolean :: i > 4);");
		$this->assertEquals("true", $result);
	}

	public function testAnyFirstElementMatches(): void {
		$result = $this->executeCodeSnippet("[10, 1, 2]->any(^i: Integer => Boolean :: i > 4);");
		$this->assertEquals("true", $result);
	}

	public function testAnyLastElementMatches(): void {
		$result = $this->executeCodeSnippet("[1, 2, 10]->any(^i: Integer => Boolean :: i > 4);");
		$this->assertEquals("true", $result);
	}

	public function testAnyWithStrings(): void {
		$result = $this->executeCodeSnippet("['hello', 'world']->any(^s: String => Boolean :: s->startsWith('w'));");
		$this->assertEquals("true", $result);
	}

	public function testAnyInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "[1, 2]->any(5);");
	}

	public function testAnyInvalidParameterParameterType(): void {
		$this->executeErrorCodeSnippet("The parameter type (Integer[1]|String['a']) of the callback function is not a subtype of Boolean",
			"[1, 'a']->any(^Boolean => Boolean :: true);");
	}

	public function testAnyInvalidParameterReturnType(): void {
		$this->executeErrorCodeSnippet("Invalid parameter type",
			"[1, 2]->any(^Any => Real :: 3.14);");
	}

}
