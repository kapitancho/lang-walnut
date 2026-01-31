<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\EnumerationValue;

interface EnumerationSubsetType extends Type {
	public EnumerationType $enumeration { get; }
	/** @param array<string, EnumerationValue> $subsetValues */
	public array $subsetValues { get; }
}