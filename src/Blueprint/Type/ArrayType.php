<?php

namespace Walnut\Lang\Blueprint\Type;

use Walnut\Lang\Blueprint\Range\LengthRange;

interface ArrayType extends Type {
	public Type $itemType { get; }
	public LengthRange $range { get; }
}