<?php

namespace Walnut\Lang\Blueprint\Type;

use Walnut\Lang\Blueprint\Value\BooleanValue;

interface FalseType extends EnumerationSubsetType {
    public function enumeration(): BooleanType;

    /** @return array<string, BooleanValue> */
    public function subsetValues(): array;
}