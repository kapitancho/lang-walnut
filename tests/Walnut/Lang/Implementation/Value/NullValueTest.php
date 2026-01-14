<?php

namespace Walnut\Lang\Test\Implementation\Value;

use Walnut\Lang\Test\BaseProgramTestHelper;

final class NullValueTest extends BaseProgramTestHelper {

	public function testNullValue(): void {
		$nullType = $this->typeRegistry->null;
		$nullValue = $this->valueRegistry->null;

		self::assertEquals($nullType, $nullValue->type);
		self::assertNull($nullValue->literalValue);
		self::assertTrue($nullValue->equals($nullType->value));
		self::assertFalse($nullValue->equals($this->valueRegistry->true));
	}
}