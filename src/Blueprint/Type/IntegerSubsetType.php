<?php

namespace Walnut\Lang\Blueprint\Type;

use Walnut\Lang\Blueprint\Value\IntegerValue;

interface IntegerSubsetType extends IntegerType {
	/** @param array<string, IntegerValue> $subsetValues */
	public array $subsetValues { get; }
}