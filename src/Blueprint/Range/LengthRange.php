<?php

namespace Walnut\Lang\Blueprint\Range;

use Stringable;

interface LengthRange extends Stringable {
	public int $minLength { get; }
	public int|PlusInfinity $maxLength { get; }

	public function isSubRangeOf(LengthRange $range): bool;

	public function lengthInRange(int $length): bool;
}