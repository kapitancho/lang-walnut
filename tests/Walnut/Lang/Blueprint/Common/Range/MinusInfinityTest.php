<?php

namespace Walnut\Lang\Test\Blueprint\Common\Range;

use PHPUnit\Framework\TestCase;
use Walnut\Lang\Blueprint\Common\Range\MinusInfinity;

final class MinusInfinityTest extends TestCase {
	public function testMinusInfinitySerialization(): void {
		$this->assertEquals('"MinusInfinity"', json_encode(MinusInfinity::value));
	}
}
