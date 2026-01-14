<?php

namespace Walnut\Lang\Blueprint\Type;

use BcMath\Number;
use Walnut\Lang\Blueprint\Common\Range\NumberRange;

interface IntegerType extends SimpleType {
	public NumberRange $numberRange { get; }
	public function contains(int|Number $value): bool;
}