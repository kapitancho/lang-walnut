<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Value;

use BcMath\Number;
use Walnut\Lang\Almond\Engine\Blueprint\Type\IntegerSubsetType;

interface IntegerValue extends Value {
	public IntegerSubsetType $type { get; }
	public Number $literalValue { get; }

	public function asRealValue(): RealValue;
}
