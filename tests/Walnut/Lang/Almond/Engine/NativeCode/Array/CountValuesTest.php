<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Array;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class CountValuesTest extends CodeExecutionTestHelper {

	public function testCountValuesEmpty(): void {
		$result = $this->executeCodeSnippet("[]->countValues;");
		$this->assertEquals("[:]", $result);
	}

	public function testCountValuesNonEmptyInteger(): void {
		$result = $this->executeCodeSnippet("[1, 2, 2, 2]->countValues;");
		$this->assertEquals("[1: 1, 2: 3]", $result);
	}

	public function testCountValuesNonEmptyString(): void {
		$result = $this->executeCodeSnippet("['a', 'b', 'b', 'b']->countValues;");
		$this->assertEquals("[a: 1, b: 3]", $result);
	}

	public function testCountValuesKeyTypeInteger(): void {
		$result = $this->executeCodeSnippet(
			"fn[1, 2, 2, 2];",
			valueDeclarations: "fn = ^m: Array<Integer<0..99>, ..4> => Map<String<1..2>:Integer<1..4>, ..4> :: m->countValues;"
		);
		$this->assertEquals("[1: 1, 2: 3]", $result);
	}

	public function testCountValuesKeyTypeString(): void {
		$result = $this->executeCodeSnippet(
			"fn['a', 'b', 'b', 'b'];",
			valueDeclarations: "fn = ^m: Array<String<1>, ..4> => Map<String<1>:Integer<1..4>, ..4> :: m->countValues;"
		);
		$this->assertEquals("[a: 1, b: 3]", $result);
	}

	public function testCountValuesKeyTypeStringSubset(): void {
		$result = $this->executeCodeSnippet(
			"fn['a', 'b', 'b', 'b'];",
			valueDeclarations: "fn = ^m: Array<String['a', 'b', 'c'], ..4> => Map<String<1>:Integer<1..4>, ..4> :: m->countValues;"
		);
		$this->assertEquals("[a: 1, b: 3]", $result);
	}

	public function testCountValuesInvalidTargetType(): void {
		$this->executeErrorCodeSnippet('Invalid target type', "[1, 'a']->countValues;");
	}
}