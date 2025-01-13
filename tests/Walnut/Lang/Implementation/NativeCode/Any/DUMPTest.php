<?php

namespace Walnut\Lang\Test\Implementation\NativeCode\Any;

use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Test\Implementation\BaseProgramTestHelper;

final class DUMPTest extends BaseProgramTestHelper {

	private function callDUMP(Value $value, string $expected): void {
		ob_start();
		$this->testMethodCall(
			$this->expressionRegistry->constant($value),
			'DUMP',
			$this->expressionRegistry->constant($this->valueRegistry->null),
			$value
		);
		$result = ob_get_clean();
		$this->assertEquals($expected, $result);
	}

	public function testDUMP(): void {
		$this->callDUMP(
			$this->valueRegistry->tuple([
				$this->valueRegistry->integer(1),
				$this->valueRegistry->string("Hello")
			]),
			"[1, 'Hello']"
		);
	}
}
