<?php

namespace Walnut\Lang\Implementation\NativeCode\Any;

use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Test\Implementation\BaseProgramTestHelper;

final class PrintedTest extends BaseProgramTestHelper {

	private function callPrinted(Value $value, string $expected): void {
		$this->testMethodCall(
			$this->expressionRegistry->constant($value),
			'printed',
			$this->expressionRegistry->constant($this->valueRegistry->null()),
			$this->valueRegistry->string($expected)
		);
	}

	public function testPrinted(): void {
		$this->callPrinted(
			$this->valueRegistry->tuple([
				$this->valueRegistry->integer(1),
				$this->valueRegistry->string("Hello")
			]),
			"[1, 'Hello']"
		);
	}
}
