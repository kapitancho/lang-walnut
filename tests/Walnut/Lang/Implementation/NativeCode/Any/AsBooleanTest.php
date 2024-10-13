<?php

namespace Walnut\Lang\Implementation\NativeCode\Any;

use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Test\Implementation\BaseProgramTestHelper;

final class AsBooleanTest extends BaseProgramTestHelper {

	private function callAsBoolean(Value $value, bool $expected): void {
		$this->testMethodCall(
			$this->expressionRegistry->constant($value),
			'asBoolean',
			$this->expressionRegistry->constant($this->valueRegistry->null()),
			$this->valueRegistry->boolean($expected)
		);
	}

	public function testAsBoolean(): void {
		$this->callAsBoolean($this->valueRegistry->integer(123), true);
		$this->callAsBoolean($this->valueRegistry->integer(0), false);
		$this->callAsBoolean($this->valueRegistry->real(3.14), true);
		$this->callAsBoolean($this->valueRegistry->real(0), false);
		$this->callAsBoolean($this->valueRegistry->string("Hello"), true);
		$this->callAsBoolean($this->valueRegistry->string(""), false);
		$this->callAsBoolean($this->valueRegistry->true(), true);
		$this->callAsBoolean($this->valueRegistry->false(), false);
		$this->callAsBoolean($this->valueRegistry->null(), false);
		$this->callAsBoolean($this->valueRegistry->tuple([
			$this->valueRegistry->integer(123)
		]), true);
		$this->callAsBoolean($this->valueRegistry->tuple([]), false);
		$this->callAsBoolean($this->valueRegistry->record([
			$this->valueRegistry->string("")
		]), true);
		$this->callAsBoolean($this->valueRegistry->record([]), false);
	}
}
