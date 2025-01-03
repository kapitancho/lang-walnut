<?php

namespace Walnut\Lang\Implementation\NativeCode\String;

use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Value\ErrorValue;
use Walnut\Lang\Blueprint\Value\StringValue;
use Walnut\Lang\Test\Implementation\BaseProgramTestHelper;

final class AsIntegerTest extends BaseProgramTestHelper {

	private function callAsInteger(StringValue $value, int|ErrorValue $expected): void {
		$this->testMethodCall(
			$this->expressionRegistry->constant($value),
			'as',
			$this->expressionRegistry->constant($this->valueRegistry->type(
				$this->typeRegistry->integer()
			)),
			$expected instanceof ErrorValue ? $expected : $this->valueRegistry->integer($expected)
		);
	}

	public function testAsInteger(): void {
		$this->typeRegistry->addAtom(new TypeNameIdentifier('NotANumber'));
		$nan = $this->valueRegistry->error(
			$this->valueRegistry->atom(new TypeNameIdentifier('NotANumber'))
		);

		$this->callAsInteger($this->valueRegistry->string("123"), 123);
		$this->callAsInteger($this->valueRegistry->string("0"), 0);
		$this->callAsInteger($this->valueRegistry->string("-42"), -42);
		$this->callAsInteger($this->valueRegistry->string("3.14"), $nan);
		$this->callAsInteger($this->valueRegistry->string("3 days"), $nan);
		$this->callAsInteger($this->valueRegistry->string("hello"), $nan);
	}
}
