<?php

namespace Walnut\Lang\Blueprint\Type;

use Walnut\Lang\Blueprint\Value\EnumerationValue;

interface EnumerationSubsetType extends Type {
    public function enumeration(): EnumerationType;

    /** @return array<string, EnumerationValue> */
    public function subsetValues(): array;
}