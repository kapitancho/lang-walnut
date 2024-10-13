<?php

namespace Walnut\Lang\Blueprint\Range;

use Stringable;

interface LengthRange extends Stringable {
    public function minLength(): int;
    public function maxLength(): int|PlusInfinity;
	public function isSubRangeOf(LengthRange $range): bool;

	public function lengthInRange(int $length): bool;
}