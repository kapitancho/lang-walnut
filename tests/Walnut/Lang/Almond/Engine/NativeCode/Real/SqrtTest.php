<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Real;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class SqrtTest extends CodeExecutionTestHelper {

	public function testSqrtPositive(): void {
		$result = $this->executeCodeSnippet("2->sqrt;");
		$this->assertEquals("1.4142135623", $result);
	}

	public function testSqrtPositiveExactSquare(): void {
		$result = $this->executeCodeSnippet("9->sqrt;");
		$this->assertEquals("3", $result);
	}

	public function testSqrtZero(): void {
		$result = $this->executeCodeSnippet("0->sqrt;");
		$this->assertEquals("0", $result);
	}

	public function testSqrtNegative(): void {
		$result = $this->executeCodeSnippet("-4.14->sqrt;");
		$this->assertEquals("@NotANumber", $result);
	}
}