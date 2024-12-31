<?php

namespace Walnut\Lang\Blueprint\Value;

use Walnut\Lang\Blueprint\Type\RealSubsetType;

interface RealValue extends LiteralValue {
	public RealSubsetType $type { get; }
	public float $literalValue { get; }
}