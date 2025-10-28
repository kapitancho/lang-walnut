<?php

namespace Walnut\Lang\Implementation\Type;

use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Test\Implementation\BaseProgramTestHelper;

final class IntegerTypeTest extends BaseProgramTestHelper {

	public function testProperties(): void {
		$realType = $this->typeRegistry->integer(min: -3, max: PlusInfinity::value);
		$this->assertTrue($realType->contains(0));
		$this->assertFalse($realType->contains(-22));
		$this->assertEquals("[-3..+Infinity)", (string)$realType->numberRange);
	}
}