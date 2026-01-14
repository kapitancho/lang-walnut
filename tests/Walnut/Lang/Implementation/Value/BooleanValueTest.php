<?php

namespace Walnut\Lang\Test\Implementation\Value;

use Walnut\Lang\Test\BaseProgramTestHelper;

final class BooleanValueTest extends BaseProgramTestHelper {

	public function testBooleanValue(): void {
		$trueType = $this->typeRegistry->true;
		$falseType = $this->typeRegistry->false;
		$trueValue = $this->valueRegistry->true;
		$falseValue = $this->valueRegistry->false;

		self::assertEquals($trueType, $trueValue->type);
		self::assertEquals($falseType, $falseValue->type);
		self::assertTrue($trueValue->literalValue);
		self::assertFalse($falseValue->literalValue);
		self::assertTrue($trueValue->equals($trueType->value));
		self::assertFalse($trueValue->equals($falseType->value));
	}
}