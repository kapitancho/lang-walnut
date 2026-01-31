<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\BooleanValue;

interface FalseType extends EnumerationSubsetType {
	public BooleanType $enumeration { get; }
	public BooleanValue $value { get; }
}