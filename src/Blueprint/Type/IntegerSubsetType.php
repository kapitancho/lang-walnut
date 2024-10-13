<?php

namespace Walnut\Lang\Blueprint\Type;

use Walnut\Lang\Blueprint\Range\IntegerRange;
use Walnut\Lang\Blueprint\Value\IntegerValue;

interface IntegerSubsetType extends Type {
    /** @return array<string, IntegerValue> */
    public function subsetValues(): array;
    public function contains(IntegerValue $value): bool;
	public function range(): IntegerRange;
}