<?php

namespace Walnut\Lang\Implementation\Type;

use BcMath\Number;
use Walnut\Lang\Test\Implementation\BaseProgramTestHelper;

final class RealSubsetTypeTest extends BaseProgramTestHelper {

	public function testProperties(): void {
		$realType = $this->typeRegistry->realSubset([
			new Number('0.0'),
			new Number('-3.14')
		]);
		$this->assertTrue($realType->contains(0.0));
		$this->assertFalse($realType->contains(-22.71));
		$this->assertEquals("0.0, -3.14", (string)$realType->numberRange);
	}
}