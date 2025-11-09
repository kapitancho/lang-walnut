<?php

namespace Walnut\Lang\Test\NativeCode\Array;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class SumTest extends CodeExecutionTestHelper {

	public function testSumEmpty(): void {
		$result = $this->executeCodeSnippet("[]->sum;");
		$this->assertEquals("0", $result);
	}

	public function testSumNonEmpty(): void {
		$result = $this->executeCodeSnippet("[1, 2]->sum;");
		$this->assertEquals("3", $result);
	}

	public function testSumNonEmptyReal(): void {
		$result = $this->executeCodeSnippet("[1, 3.14]->sum;");
		$this->assertEquals("4.14", $result);
	}

	public function testSumReturnType(): void {
		$result = $this->executeCodeSnippet(
			"mySum[1.6, 2];",
			valueDeclarations:  "mySum = ^arr: Array<Real<1.4..5.1>, 2..3> => Real<2.8..15.3> :: arr->sum;"
		);
		$this->assertEquals("3.6", $result);
	}

	public function testSumInvalidType(): void {
		$this->executeErrorCodeSnippet('Invalid target type', "['hello','world', 'hi', 'hello']->sum;");
	}
}