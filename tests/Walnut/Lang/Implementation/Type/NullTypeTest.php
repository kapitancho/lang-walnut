<?php

namespace Walnut\Lang\Test\Implementation\Type;

use Walnut\Lang\Test\BaseProgramTestHelper;

final class NullTypeTest extends BaseProgramTestHelper {

	public function testNullType(): void {
		$nullType = $this->typeRegistry->null;
		self::assertEquals('Null', $nullType->name->identifier);
		self::assertNull($nullType->value->literalValue);
		self::assertTrue($nullType->isSubtypeOf($this->typeRegistry->null));
		self::assertFalse($nullType->isSubtypeOf($this->typeRegistry->boolean));
	}
}