<?php

namespace Walnut\Lang\Blueprint\Type;

use Walnut\Lang\Blueprint\Value\BooleanValue;

interface FalseType extends EnumerationSubsetType {
	public BooleanType $enumeration { get; }
	/** @param array<string, BooleanType> $subsetValues */
	public array $subsetValues { get; }

	public BooleanValue $value { get; }
}