<?php

namespace Walnut\Lang\Test\NativeCode\Array;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class MinTest extends CodeExecutionTestHelper {

	public function testMinEmpty(): void {
		$this->executeErrorCodeSnippet('Invalid target type', "[]->min;");
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
		$this->executeErrorCodeSnippet('Invalid target type', "['hello','world', 'hi', 'hello']->min;");
	}
}