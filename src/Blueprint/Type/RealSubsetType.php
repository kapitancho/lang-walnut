<?php

namespace Walnut\Lang\Blueprint\Type;

use Walnut\Lang\Blueprint\Common\Range\RealRange;
use Walnut\Lang\Blueprint\Value\RealValue;

interface RealSubsetType extends Type {
	/** @param array<string, RealRange> $subsetValues */
	public array $subsetValues { get; }
	public RealRange $range { get; }

    public function contains(RealValue $value): bool;
}