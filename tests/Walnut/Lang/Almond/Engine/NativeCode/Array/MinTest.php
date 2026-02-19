<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Array;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class MinTest extends CodeExecutionTestHelper {

	public function testMinEmpty(): void {
		$this->executeErrorCodeSnippet('The array must have at least one item.', "[]->min;");
	}

	public function testMinNonEmpty(): void {
		$result = $this->executeCodeSnippet("[1, 2]->min;");
		$this->assertEquals("1", $result);
	}

	public function testMinNonEmptyReversed(): void {
		$result = $this->executeCodeSnippet("[2, 1]->min;");
		$this->assertEquals("1", $result);
	}

	public function testMinNonEmptyReal(): void {
		$result = $this->executeCodeSnippet("[1, 3.14]->min;");
		$this->assertEquals("1", $result);
	}

	public function testMinInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "[1, 3.14]->max('hello');");
	}

	public function testMinInvalidTargetType(): void {
		$this->executeErrorCodeSnippet("The item type of the target array must be a subtype of Real, got String['hello', 'world', 'hi']",
			"['hello','world', 'hi', 'hello']->min;");
	}
}