<?php

namespace Walnut\Lang\Blueprint\Type;

use Walnut\Lang\Blueprint\Value\BooleanValue;

interface TrueType extends EnumerationSubsetType {
    public function enumeration(): BooleanType;

    /** @return array<string, BooleanValue> */
    public function subsetValues(): array;

	public function value(): BooleanValue;
}