<?php

namespace Walnut\Lang\Blueprint\Type;

use Walnut\Lang\Blueprint\Common\Range\NumberRange;
use Walnut\Lang\Blueprint\Value\IntegerValue;
use Walnut\Lang\Blueprint\Value\RealValue;

interface RealType extends Type {
	public NumberRange $numberRange { get; }
	public function contains(IntegerValue|RealValue $value): bool;
}