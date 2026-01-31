<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\RealValue;

interface RealSubsetType extends RealType {
	/** @var list<RealValue> */
	public array $subsetValues { get; }
}
