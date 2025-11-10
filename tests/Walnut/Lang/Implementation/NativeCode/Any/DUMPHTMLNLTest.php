<?php

namespace Walnut\Lang\Implementation\NativeCode\Any;

use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Test\Implementation\BaseProgramTestHelper;

final class DUMPHTMLNLTest extends BaseProgramTestHelper {

	private function callDUMPHTMLNL(Value $value, string $expected): void {
		ob_start();
		$this->testMethodCall(
			$this->expressionRegistry->constant($value),
			'DUMPHTMLNL',
			$this->expressionRegistry->constant($this->valueRegistry->null),
			$value
		);
		$result = ob_get_clean();
		$this->assertEquals($expected, $result);
	}

	public function testDUMP(): void {
		$this->callDUMPHTMLNL(
			$this->valueRegistry->tuple([
				$this->valueRegistry->integer(1),
				$this->valueRegistry->string("Hello")
			]),
			"[1, &#039;Hello&#039;]<br/>" . PHP_EOL
		);
	}
}
