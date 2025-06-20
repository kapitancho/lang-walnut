<?php

namespace Walnut\Lang\Blueprint\Type;

use Walnut\Lang\Blueprint\Common\Range\NumberRange;
use Walnut\Lang\Blueprint\Value\IntegerValue;

interface IntegerType extends Type {
	public NumberRange $numberRange { get; }
	public function contains(IntegerValue $value): bool;
}