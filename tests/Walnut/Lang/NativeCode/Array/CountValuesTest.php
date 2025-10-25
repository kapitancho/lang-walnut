<?php

namespace Walnut\Lang\Test\NativeCode\Array;

use Walnut\Lang\Test\CodeExecutionTestHelper;

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

	public function testCountValuesInvalidTargetType(): void {
		$this->executeErrorCodeSnippet('Invalid target type', "[1, 'a']->countValues;");
	}
}