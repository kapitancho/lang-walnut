<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\BooleanType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\FalseType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TrueType;

interface BooleanValue extends EnumerationValue {
	public BooleanType $enumeration { get; }
	public TrueType|FalseType $type { get; }
	public bool $literalValue { get; }
}