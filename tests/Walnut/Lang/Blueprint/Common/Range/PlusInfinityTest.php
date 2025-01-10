<?php

namespace Walnut\Lang\Blueprint\Common\Range;

use PHPUnit\Framework\TestCase;

final class PlusInfinityTest extends TestCase {
	public function testMinusInfinitySerialization(): void {
		$this->assertEquals('"PlusInfinity"', json_encode(PlusInfinity::value));
	}
}
