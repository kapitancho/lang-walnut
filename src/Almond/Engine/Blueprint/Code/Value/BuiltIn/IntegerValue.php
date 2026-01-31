<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn;

use BcMath\Number;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\IntegerSubsetType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;

interface IntegerValue extends Value {
	public IntegerSubsetType $type { get; }
	public Number $literalValue { get; }

	public function asRealValue(): RealValue;
}
