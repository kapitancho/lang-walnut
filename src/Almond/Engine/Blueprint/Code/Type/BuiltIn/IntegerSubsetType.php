<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\IntegerValue;

interface IntegerSubsetType extends IntegerType {
	/** @var list<IntegerValue> */
	public array $subsetValues { get; }
}
