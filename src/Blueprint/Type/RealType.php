<?php

namespace Walnut\Lang\Blueprint\Type;

use BcMath\Number;
use Walnut\Lang\Blueprint\Common\Range\NumberRange;

interface RealType extends Type {
	public NumberRange $numberRange { get; }
	public function contains(int|float|Number $value): bool;
}