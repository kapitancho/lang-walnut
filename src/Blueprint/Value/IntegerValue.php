<?php

namespace Walnut\Lang\Blueprint\Value;

use Walnut\Lang\Blueprint\Type\IntegerSubsetType;

interface IntegerValue extends LiteralValue {
    public function type(): IntegerSubsetType;
    public function literalValue(): int;

	public function asRealValue(): RealValue;
}