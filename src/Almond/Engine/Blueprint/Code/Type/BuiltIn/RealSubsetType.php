<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn;

use BcMath\Number;

interface RealSubsetType extends RealType {
	/** @var list<Number> */
	public array $subsetValues { get; }
}
