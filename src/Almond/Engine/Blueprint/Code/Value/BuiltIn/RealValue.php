<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn;

use BcMath\Number;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\RealSubsetType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;

interface RealValue extends Value {
	public RealSubsetType $type { get; }
	public Number $literalValue { get; }
}
