<?php

namespace Walnut\Lang\Blueprint\Type;

use Walnut\Lang\Blueprint\Range\RealRange;
use Walnut\Lang\Blueprint\Value\RealValue;

interface RealSubsetType extends Type {
    /** @return array<string, RealValue> */
    public function subsetValues(): array;
    public function contains(RealValue $value): bool;
	public function range(): RealRange;
}