<?php

namespace Walnut\Lang;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class OperatorPriorityTest extends CodeExecutionTestHelper {
	public function test1(): void {
		$result = $this->executeCodeSnippet('2 + 3 + 4;');
		$this->assertEquals('9', $result);
	}

	public function test2(): void {
		$result = $this->executeCodeSnippet('2 - 3 - 4;');
		$this->assertEquals('-5', $result);
	}
	public function test3(): void {
		$result = $this->executeCodeSnippet('2 + 3 * 4;');
		$this->assertEquals('14', $result);
	}
	public function test4(): void {
		$result = $this->executeCodeSnippet('2 * 3 + 4;');
		$this->assertEquals('10', $result);
	}
	public function test5(): void {
		$result = $this->executeCodeSnippet('2 - 3 + 4;');
		$this->assertEquals('3', $result);
	}
	public function test6(): void {
		$result = $this->executeCodeSnippet('1 + 9 - 12 * 3 / 6 - 5 / 4 + 11;');
		$this->assertEquals('13.75', $result);
	}
	public function test7(): void {
		$result = $this->executeCodeSnippet("'test'->length->sqrt;");
		$this->assertEquals('2', $result);
	}
	public function test8(): void {
		$result = $this->executeCodeSnippet("x.a + y.b->sqrt;", "x = [a: 1, b: 2]; y = [a: 3, b: 4];");
		$this->assertEquals('3', $result);
	}
	public function test9(): void {
		$result = $this->executeCodeSnippet('1 + 9->sqrt * 2;');
		$this->assertEquals('7', $result);
	}
	public function test10(): void {
		$result = $this->executeCodeSnippet('1 + 9->sqrt(null) * 2;');
		$this->assertEquals('7', $result);
	}

}