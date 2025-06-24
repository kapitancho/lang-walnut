<?php

namespace Walnut\Lang\Implementation\Common\Range;

use BcMath\Number;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Walnut\Lang\Blueprint\Common\Range\InvalidLengthRange;
use Walnut\Lang\Blueprint\Common\Range\InvalidNumberRange;
use Walnut\Lang\Blueprint\Common\Range\MinusInfinity;
use Walnut\Lang\Blueprint\Common\Range\PlusInfinity;

final class LengthRangeTest extends TestCase {

	public function testInvalidLengthRange(): void {
		$this->expectException(InvalidLengthRange::class);
		new LengthRange(new Number(15), new Number(-2));
	}

}