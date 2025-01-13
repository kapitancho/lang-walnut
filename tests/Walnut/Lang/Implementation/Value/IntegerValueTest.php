<?php

namespace Walnut\Lang\Test\Implementation\Value;

use Walnut\Lang\Test\Implementation\BaseProgramTestHelper;

final class IntegerValueTest extends BaseProgramTestHelper {

	public function testIntegerAsReal(): void {
		$int = $this->valueRegistry->integer(5);
		$real = $int->asRealValue();
		$this->assertSame((float)(string)$real->literalValue, 5.0);
	}
}