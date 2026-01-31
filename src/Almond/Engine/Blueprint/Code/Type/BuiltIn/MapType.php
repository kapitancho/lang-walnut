<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\LengthRange;

interface MapType extends Type {
	public Type $keyType { get; }
	public Type $itemType { get; }
	public LengthRange $range { get; }
}