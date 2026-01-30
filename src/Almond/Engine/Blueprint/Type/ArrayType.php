<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Type;

use Walnut\Lang\Almond\Engine\Blueprint\Range\LengthRange;

interface ArrayType extends Type {
	public Type $itemType { get; }
	public LengthRange $range { get; }
}