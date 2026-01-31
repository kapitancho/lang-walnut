<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn;

use BcMath\Number;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\NumberRange;

interface IntegerType extends Type {
	public NumberRange $numberRange { get; }
	public function contains(int|Number $value): bool;
}
