<?php

namespace Walnut\Lang\Blueprint\Common\Range;

use BcMath\Number;
use Stringable;

interface LengthRange extends Stringable {
	public Number $minLength { get; }
	public Number|PlusInfinity $maxLength { get; }

	public function tryRangeIntersectionWith(LengthRange $range): LengthRange|null;

	public function isSubRangeOf(LengthRange $range): bool;

	public function lengthInRange(Number $length): bool;
}