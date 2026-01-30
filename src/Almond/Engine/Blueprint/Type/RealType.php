<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Type;

use BcMath\Number;
use Walnut\Lang\Almond\Engine\Blueprint\Range\NumberRange;

interface RealType extends Type {
	public NumberRange $numberRange { get; }
	public function contains(int|float|Number $value): bool;
}
