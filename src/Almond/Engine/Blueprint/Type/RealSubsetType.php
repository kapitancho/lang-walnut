<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Type;

use Walnut\Lang\Almond\Engine\Blueprint\Value\RealValue;

interface RealSubsetType extends RealType {
	/** @var list<RealValue> */
	public array $subsetValues { get; }
}
