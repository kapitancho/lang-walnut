<?php

namespace Walnut\Lang\Blueprint\Type;

use Walnut\Lang\Blueprint\Common\Range\LengthRange;

interface SetType extends Type {
	public Type $itemType { get; }
	public LengthRange $range { get; }
}