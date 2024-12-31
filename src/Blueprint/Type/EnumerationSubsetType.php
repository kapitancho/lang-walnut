<?php

namespace Walnut\Lang\Blueprint\Type;

use Walnut\Lang\Blueprint\Value\EnumerationValue;

interface EnumerationSubsetType extends Type {
	public EnumerationType $enumeration { get; }
	/** @param array<string, EnumerationValue> $subsetValues */
	public array $subsetValues { get; }
}