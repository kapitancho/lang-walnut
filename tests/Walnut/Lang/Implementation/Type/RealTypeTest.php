<?php

namespace Walnut\Lang\Implementation\Type;

use Walnut\Lang\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Test\Implementation\BaseProgramTestHelper;

final class RealTypeTest extends BaseProgramTestHelper {

	public function testProperties(): void {
		$realType = $this->typeRegistry->real(min: -3.14, max: PlusInfinity::value);
		$this->assertTrue($realType->contains(0.0));
		$this->assertFalse($realType->contains(-22.71));
		$this->assertEquals("[-3.14..+Infinity)", (string)$realType->numberRange);
	}
}