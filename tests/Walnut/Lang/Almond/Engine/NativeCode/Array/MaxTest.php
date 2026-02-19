<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Array;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class MaxTest extends CodeExecutionTestHelper {

	public function testMaxEmpty(): void {
		$this->executeErrorCodeSnippet('The array must have at least one item.', "[]->max;");
	}

	public function testMaxNonEmpty(): void {
		$result = $this->executeCodeSnippet("[1, 2]->max;");
		$this->assertEquals("2", $result);
	}

	public function testMaxNonEmptyReversed(): void {
		$result = $this->executeCodeSnippet("[2, 1]->max;");
		$this->assertEquals("2", $result);
	}

	public function testMaxNonEmptyReal(): void {
		$result = $this->executeCodeSnippet("[1, 3.14]->max;");
		$this->assertEquals("3.14", $result);
	}

	public function testMaxInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "[1, 3.14]->max('hello');");
	}

	public function testMaxInvalidTargetType(): void {
		$this->executeErrorCodeSnippet("The item type of the target array must be a subtype of Real, got String['hello', 'world', 'hi']",
			"['hello','world', 'hi', 'hello']->max;");
	}
}