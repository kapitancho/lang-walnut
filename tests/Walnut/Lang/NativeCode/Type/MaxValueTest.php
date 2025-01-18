<?php

namespace Walnut\Lang\NativeCode\Type;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class MaxValueTest extends CodeExecutionTestHelper {

	public function testMaxValueInteger(): void {
		$result = $this->executeCodeSnippet("type{Integer<2..5>}->maxValue;");
		$this->assertEquals("5", $result);
	}

	public function testMaxValueIntegerPlusInfinity(): void {
		$result = $this->executeCodeSnippet("type{Integer<2..>}->maxValue;");
		$this->assertEquals("PlusInfinity[]", $result);
	}

	public function testMaxValueReal(): void {
		$result = $this->executeCodeSnippet("type{Real<2..5.14>}->maxValue;");
		$this->assertEquals("5.14", $result);
	}

	public function testMaxValueRealPlusInfinity(): void {
		$result = $this->executeCodeSnippet("type{Real<2..>}->maxValue;");
		$this->assertEquals("PlusInfinity[]", $result);
	}

}