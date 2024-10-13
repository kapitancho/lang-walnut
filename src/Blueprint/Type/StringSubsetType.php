<?php

namespace Walnut\Lang\Blueprint\Type;

use Walnut\Lang\Blueprint\Range\LengthRange;
use Walnut\Lang\Blueprint\Value\StringValue;

interface StringSubsetType extends Type {
    /** @return array<string, StringValue> */
    public function subsetValues(): array;
	public function contains(StringValue $value): bool;
    public function range(): LengthRange;
}