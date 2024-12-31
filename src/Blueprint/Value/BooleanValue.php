<?php

namespace Walnut\Lang\Blueprint\Value;

use Walnut\Lang\Blueprint\Type\BooleanType;

interface BooleanValue extends EnumerationValue, LiteralValue {
	public BooleanType $enumeration { get; }
	public bool $literalValue { get; }
}