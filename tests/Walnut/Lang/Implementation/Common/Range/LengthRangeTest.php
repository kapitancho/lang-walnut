<?php

namespace Walnut\Lang\Test\Implementation\Common\Range;

use BcMath\Number;
use PHPUnit\Framework\TestCase;
use Walnut\Lang\Blueprint\Common\Range\InvalidLengthRange;
use Walnut\Lang\Implementation\Common\Range\LengthRange;

final class LengthRangeTest extends TestCase {

	public function testInvalidLengthRange(): void {
		$this->expectException(InvalidLengthRange::class);
		new LengthRange(new Number(15), new Number(-2));
	}

}