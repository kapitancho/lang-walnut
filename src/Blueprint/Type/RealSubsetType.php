<?php

namespace Walnut\Lang\Blueprint\Type;

use Walnut\Lang\Blueprint\Value\RealValue;

interface RealSubsetType extends RealType {
	/** @param array<string, Number> $subsetValues */
	public array $subsetValues { get; }
}