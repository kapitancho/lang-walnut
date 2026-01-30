<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Value;

use BcMath\Number;
use Walnut\Lang\Almond\Engine\Blueprint\Type\RealSubsetType;

interface RealValue extends Value {
	public RealSubsetType $type { get; }
	public Number $literalValue { get; }
}
