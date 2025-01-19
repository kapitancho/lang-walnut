<?php

namespace Walnut\Lang\NativeCode\Mutable;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class ValueTest extends CodeExecutionTestHelper {

	public function testValue(): void {
		$result = $this->executeCodeSnippet("mutable{Real, 3.14}->value;");
		$this->assertEquals("3.14", $result);
	}

	public function testValueMetaType(): void {
		$result = $this->executeCodeSnippet("getValue(mutable{Real, 3.14});", "getValue = ^MutableValue => Any :: #->value;");
		$this->assertEquals("3.14", $result);
	}

}