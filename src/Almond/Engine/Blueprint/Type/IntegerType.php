<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Type;

use BcMath\Number;
use Walnut\Lang\Almond\Engine\Blueprint\Range\NumberRange;

interface IntegerType extends Type {
	public NumberRange $numberRange { get; }
	public function contains(int|Number $value): bool;
}
