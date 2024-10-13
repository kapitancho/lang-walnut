<?php

namespace Walnut\Lang\Blueprint\Range;

use Stringable;
use Walnut\Lang\Blueprint\Value\RealValue;

interface RealRange extends Stringable {
    public function minValue(): float|MinusInfinity;
    public function maxValue(): float|PlusInfinity;
	public function isSubRangeOf(RealRange $range): bool;

	public function contains(RealValue $value): bool;
}