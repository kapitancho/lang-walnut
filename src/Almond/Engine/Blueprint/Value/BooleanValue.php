<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Value;

use Walnut\Lang\Almond\Engine\Blueprint\Type\BooleanType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\FalseType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\TrueType;

interface BooleanValue extends EnumerationValue {
	public BooleanType $enumeration { get; }
	public TrueType|FalseType $type { get; }
	public bool $literalValue { get; }
}