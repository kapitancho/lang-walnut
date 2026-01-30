<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Type;

use Walnut\Lang\Almond\Engine\Blueprint\Value\EnumerationValue;

interface EnumerationSubsetType extends Type {
	public EnumerationType $enumeration { get; }
	/** @param array<string, EnumerationValue> $subsetValues */
	public array $subsetValues { get; }
}