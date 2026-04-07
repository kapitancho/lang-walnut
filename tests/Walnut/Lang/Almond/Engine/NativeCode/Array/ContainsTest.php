<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Array;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class ContainsTest extends CodeExecutionTestHelper {

	public function testContainsEmpty(): void {
		$result = $this->executeCodeSnippet("[]->contains(5);");
		$this->assertEquals("false", $result);
	}

	public function testContainsNonEmptyFalse(): void {
		$result = $this->executeCodeSnippet("[1, 2, 5, 10, 5]->contains(7);");
		$this->assertEquals("false", $result);
	}

	public function testContainsNonEmptyYes(): void {
		$result = $this->executeCodeSnippet("[1, 2, 5, 10, 5]->contains(5);");
		$this->assertEquals("true", $result);
	}

	public function testContainsEmptyValueDirect(): void {
		$result = $this->executeCodeSnippet(
			"getArray()->contains(empty);",
			valueDeclarations: "getArray = ^ => Array<String|Boolean, 2> :: val[true, empty, 'hello'];"
		);
		$this->assertEquals("false", $result);
	}

	public function testContainsEmptyValue(): void {
		$result = $this->executeCodeSnippet(
			"getArray[a: empty, b: 'hello', c: true]->contains(empty);",
			valueDeclarations: "getArray = ^s: [a: Optional<Integer>, b: String, c: Optional<Boolean>]
				=> Array<Integer|String|Boolean, ..3> :: [s.a, s.b, s.c];"
		);
		$this->assertEquals("false", $result);
	}

}