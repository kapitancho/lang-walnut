<?php

namespace Walnut\Lang\Blueprint\Type;

use Walnut\Lang\Blueprint\Range\LengthRange;
use Walnut\Lang\Blueprint\Value\StringValue;

interface StringSubsetType extends Type {
	/** @param array<string, LengthRange> $subsetValues */
	public array $subsetValues { get; }
	public LengthRange $range { get; }

	public function contains(StringValue $value): bool;
}