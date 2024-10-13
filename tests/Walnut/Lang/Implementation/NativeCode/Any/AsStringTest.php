<?php

namespace Walnut\Lang\Implementation\NativeCode\Any;

use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Test\Implementation\BaseProgramTestHelper;

final class AsStringTest extends BaseProgramTestHelper {

	private function callAsString(Value $value, string $expected): void {
		$this->testMethodCall(
			$this->expressionRegistry->constant($value),
			'asString',
			$this->expressionRegistry->constant($this->valueRegistry->null()),
			$this->valueRegistry->string($expected)
		);
	}

    private function analyseCallAsString(Type $type, Type $expected): void {
        $this->testMethodCallAnalyse(
            $type,
            'asString',
            $this->typeRegistry->null(),
            $expected
        );
    }

	public function testAsString(): void {
		$this->callAsString($this->valueRegistry->integer(123), "123");
		$this->callAsString($this->valueRegistry->integer(0), "0");
		$this->callAsString($this->valueRegistry->real(3.14), "3.14");
		$this->callAsString($this->valueRegistry->real(0), "0");
		$this->callAsString($this->valueRegistry->string("Hello"), "Hello");
		$this->callAsString($this->valueRegistry->string(""), "");
		$this->callAsString($this->valueRegistry->true(), "true");
		$this->callAsString($this->valueRegistry->false(), "false");
		$this->callAsString($this->valueRegistry->null(), "null");

        $this->analyseCallAsString(
            $this->typeRegistry->integer(-5, 42),
            $this->typeRegistry->string(1, 2)
        );
	}
}
