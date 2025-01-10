<?php

namespace Walnut\Lang\Blueprint\Common\Range;

use PHPUnit\Framework\TestCase;

final class MinusInfinityTest extends TestCase {
	public function testMinusInfinitySerialization(): void {
		$this->assertEquals('"MinusInfinity"', json_encode(MinusInfinity::value));
	}
}
