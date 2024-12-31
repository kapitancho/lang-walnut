<?php

namespace Walnut\Lang\Blueprint\Value;

use Walnut\Lang\Blueprint\Type\IntegerSubsetType;

interface IntegerValue extends LiteralValue {
	public IntegerSubsetType $type { get; }
	public int $literalValue { get; }

	public function asRealValue(): RealValue;
}