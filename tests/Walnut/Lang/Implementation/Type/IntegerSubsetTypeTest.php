<?php

namespace Walnut\Lang\Implementation\Type;

use BcMath\Number;
use Walnut\Lang\Test\Implementation\BaseProgramTestHelper;

final class IntegerSubsetTypeTest extends BaseProgramTestHelper {

	public function testProperties(): void {
		$realType = $this->typeRegistry->realSubset([
			new Number('0'),
			new Number('-3')
		]);
		$this->assertTrue($realType->contains(0));
		$this->assertFalse($realType->contains(-22));
		$this->assertEquals("0, -3", (string)$realType->numberRange);
	}
}