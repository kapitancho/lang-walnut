<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn;

use BcMath\Number;

interface IntegerSubsetType extends IntegerType {
	/** @var list<Number> */
	public array $subsetValues { get; }
}
