<?php

namespace Walnut\Lang\NativeCode\Type;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class MinValueTest extends CodeExecutionTestHelper {

	public function testMinValueInteger(): void {
		$result = $this->executeCodeSnippet("type{Integer<2..5>}->minValue;");
		$this->assertEquals("2", $result);
	}

	public function testMinValueIntegerMinusInfinity(): void {
		$result = $this->executeCodeSnippet("type{Integer<..5>}->minValue;");
		$this->assertEquals("MinusInfinity[]", $result);
	}

	public function testMinValueReal(): void {
		$result = $this->executeCodeSnippet("type{Real<2.14..5>}->minValue;");
		$this->assertEquals("2.14", $result);
	}

	public function testMinValueRealMinusInfinity(): void {
		$result = $this->executeCodeSnippet("type{Real<..5>}->minValue;");
		$this->assertEquals("MinusInfinity[]", $result);
	}

}