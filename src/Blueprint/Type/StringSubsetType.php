<?php

namespace Walnut\Lang\Blueprint\Type;

use Walnut\Lang\Blueprint\Common\Range\LengthRange;
use Walnut\Lang\Blueprint\Value\StringValue;

interface StringSubsetType extends Type {
	/** @param array<string, string> $subsetValues */
	public array $subsetValues { get; }
	public LengthRange $range { get; }

	public function contains(string $value): bool;
}