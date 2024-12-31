<?php

namespace Walnut\Lang\Blueprint\Value;

use BcMath\Number;
use Walnut\Lang\Blueprint\Type\RealSubsetType;

interface RealValue extends LiteralValue {
	public RealSubsetType $type { get; }
	public Number $literalValue { get; }
}