<?php

namespace Walnut\Lang\Blueprint\Type;

use Walnut\Lang\Blueprint\Value\NullValue;

interface NullType extends AtomType, SimpleType {
	public NullValue $value { get; }
}