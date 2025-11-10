<?php

namespace Walnut\Lang\Implementation\NativeCode\Any;

use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Test\Implementation\BaseProgramTestHelper;

final class DUMPNLTest extends BaseProgramTestHelper {

	private function callDUMPNL(Value $value, string $expected): void {
		ob_start();
		$this->testMethodCall(
			$this->expressionRegistry->constant($value),
			'DUMPNL',
			$this->expressionRegistry->constant($this->valueRegistry->null),
			$value
		);
		$result = ob_get_clean();
		$this->assertEquals($expected, $result);
	}

	public function testDUMP(): void {
		$this->callDUMPNL(
			$this->valueRegistry->tuple([
				$this->valueRegistry->integer(1),
				$this->valueRegistry->string("Hello")
			]),
			"[1, 'Hello']<br/>" . PHP_EOL
		);
	}
}
