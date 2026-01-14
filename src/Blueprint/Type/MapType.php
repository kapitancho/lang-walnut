<?php

namespace Walnut\Lang\Blueprint\Type;

use Walnut\Lang\Blueprint\Common\Range\LengthRange;

interface MapType extends CompositeType {
	public Type $keyType { get; }
	public Type $itemType { get; }
	public LengthRange $range { get; }
}