<?php

namespace Walnut\Lang\Blueprint\Range;

use Stringable;
use Walnut\Lang\Blueprint\Value\RealValue;

interface RealRange extends Stringable {
	public float|MinusInfinity $minValue { get; }
	public float|PlusInfinity $maxValue { get; }

	public function isSubRangeOf(RealRange $range): bool;

	public function contains(RealValue $value): bool;
}