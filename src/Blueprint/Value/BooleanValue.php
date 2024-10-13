<?php

namespace Walnut\Lang\Blueprint\Value;

use Walnut\Lang\Blueprint\Type\BooleanType;

interface BooleanValue extends EnumerationValue, LiteralValue {
	public function enumeration(): BooleanType;
    public function literalValue(): bool;
}