<?php

namespace Walnut\Lang\Blueprint\Value;

use BcMath\Number;
use Walnut\Lang\Blueprint\Type\IntegerSubsetType;

interface IntegerValue extends LiteralValue {
	public IntegerSubsetType $type { get; }
	public Number $literalValue { get; }

	public function asRealValue(): RealValue;
}