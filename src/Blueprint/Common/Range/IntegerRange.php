<?php

namespace Walnut\Lang\Blueprint\Common\Range;

use BcMath\Number;
use Stringable;

interface IntegerRange extends Stringable {
	public Number|MinusInfinity $minValue { get; }
	public Number|PlusInfinity $maxValue { get; }

	public function isSubRangeOf(IntegerRange $range): bool;
	public function intersectsWith(IntegerRange $range): bool;
	public function tryRangeUnionWith(IntegerRange $range): IntegerRange|null;
	public function tryRangeIntersectionWith(IntegerRange $range): IntegerRange|null;

    public function asRealRange(): RealRange;

    public function contains(Number $value): bool;
}