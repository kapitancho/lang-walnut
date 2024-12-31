<?php

namespace Walnut\Lang\Blueprint\Range;

use Stringable;
use Walnut\Lang\Blueprint\Value\IntegerValue;

interface IntegerRange extends Stringable {
	public int|MinusInfinity $minValue { get; }
	public int|PlusInfinity $maxValue { get; }

	public function isSubRangeOf(IntegerRange $range): bool;
	public function intersectsWith(IntegerRange $range): bool;
	public function tryRangeUnionWith(IntegerRange $range): IntegerRange|null;
	public function tryRangeIntersectionWith(IntegerRange $range): IntegerRange|null;

    public function asRealRange(): RealRange;

    public function contains(IntegerValue $value): bool;
}