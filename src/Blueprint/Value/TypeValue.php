<?php

namespace Walnut\Lang\Blueprint\Value;

use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Type\TypeType;

interface TypeValue extends Value {
	public TypeType $type { get; }
	public Type $typeValue { get; }
}