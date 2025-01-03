<?php

namespace Walnut\Lang\Blueprint\Type;

use Walnut\Lang\Blueprint\Common\Range\IntegerRange;
use Walnut\Lang\Blueprint\Value\IntegerValue;

interface IntegerSubsetType extends Type {
	/** @param array<string, IntegerValue> $subsetValues */
	public array $subsetValues { get; }
	public IntegerRange $range { get; }

    public function contains(IntegerValue $value): bool;
}