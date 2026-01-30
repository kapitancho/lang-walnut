<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Type;

use Walnut\Lang\Almond\Engine\Blueprint\Range\LengthRange;

interface MapType extends Type {
	public Type $keyType { get; }
	public Type $itemType { get; }
	public LengthRange $range { get; }
}