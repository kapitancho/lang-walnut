<?php

namespace Walnut\Lang\Blueprint\Common\Range;

use BcMath\Number;
use Stringable;

interface NumberRange extends Stringable {
	/** @var array<NumberInterval> */
	public array $intervals { get; }

	public bool $isIntegerRange { get; }

	public NumberIntervalEndpoint|MinusInfinity $min { get; }
	public NumberIntervalEndpoint|PlusInfinity $max { get; }

	public function contains(Number $value): bool;
	public function containsInterval(NumberInterval $interval): bool;
	public function containsRange(NumberRange $range): bool;

	public function intersectionWith(NumberRange $other): NumberRange|null;
	public function unionWith(NumberRange $other): NumberRange;
}