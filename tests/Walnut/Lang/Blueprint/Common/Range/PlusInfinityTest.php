<?php

namespace Walnut\Lang\Test\Blueprint\Common\Range;

use PHPUnit\Framework\TestCase;
use Walnut\Lang\Blueprint\Common\Range\PlusInfinity;

final class PlusInfinityTest extends TestCase {
	public function testMinusInfinitySerialization(): void {
		$this->assertEquals('"PlusInfinity"', json_encode(PlusInfinity::value));
	}
}
