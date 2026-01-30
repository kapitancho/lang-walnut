<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Type;

use Walnut\Lang\Almond\Engine\Blueprint\Value\BooleanValue;

interface TrueType extends EnumerationSubsetType {
	public BooleanType $enumeration { get; }
	public BooleanValue $value { get; }
}