<?php

namespace Walnut\Lang\Blueprint\Common\Range;

use BcMath\Number;
use Stringable;

interface RealRange extends Stringable {
	public Number|MinusInfinity $minValue { get; }
	public Number|PlusInfinity $maxValue { get; }

	public function isSubRangeOf(RealRange $range): bool;

	public function contains(Number $value): bool;
}