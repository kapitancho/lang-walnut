<?php

namespace Walnut\Lang\Blueprint\Common\Range;

use BcMath\Number;
use Stringable;

interface NumberInterval extends Stringable {
	public MinusInfinity|NumberIntervalEndpoint $start { get; }
	public PlusInfinity|NumberIntervalEndpoint $end { get; }

	public function contains(Number $value): bool;
	public function containsInterval(NumberInterval $other): bool;

	public function intersectsWith(NumberInterval $other, bool $asIntegerInterval = false): bool;
	public function intersectionWith(NumberInterval $other, bool $asIntegerInterval): NumberInterval|null;

	public function unitesWith(NumberInterval $other, bool $asIntegerInterval = false): bool;
	public function unionWith(NumberInterval $other, bool $asIntegerInterval): NumberInterval|null;
}