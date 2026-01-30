<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Type;

use Walnut\Lang\Almond\Engine\Blueprint\Value\IntegerValue;

interface IntegerSubsetType extends IntegerType {
	/** @var list<IntegerValue> */
	public array $subsetValues { get; }
}
